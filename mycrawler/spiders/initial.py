import scrapy

def find_nth(haystack, needle, n):
    start = haystack.find(needle)
    while start >= 0 and n > 1:
        start = haystack.find(needle, start+len(needle))
        n -= 1
    return start

class DmozSpider(scrapy.Spider):
    name = "initial"
    allowed_domains = ["registrar.ucla.edu"]
    start_urls = ["http://www.registrar.ucla.edu/schedule/"]

    def parse(self, response):
        filename = "classes.txt"
        classes = response.xpath('//select[@id="ctl00_BodyContentPlaceHolder_SOCmain_lstSubjectArea"]/option').extract()
        ids = []
        for myclass in enumerate(classes):
            endingIndex = find_nth(myclass[1], '"', 2);
            ids.append(myclass[1][15:endingIndex])
        for index, myId in enumerate(ids):
            ids[index] = myId.replace(" ", "+")
        idsString = ",".join(ids)
        with open(filename, 'wb') as f:
            f.write(idsString)
