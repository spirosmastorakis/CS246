import scrapy
from util import find_nth

class DmozSpider(scrapy.Spider):
    name = "initial"
    allowed_domains = ["registrar.ucla.edu"]
    start_urls = ["http://www.registrar.ucla.edu/schedule/"]
    filename = "results/classes.txt"

    def parse(self, response):
        classes = response.xpath('//select[@id="ctl00_BodyContentPlaceHolder_SOCmain_lstSubjectArea"]/option').extract()
        ids = []
        for myclass in enumerate(classes):
            endingIndex = find_nth(myclass[1], '"', 2);
            ids.append(myclass[1][15:endingIndex])
        for index, myId in enumerate(ids):
            ids[index] = myId.replace(" ", "+")
        idsString = "\n".join(ids)
        with open(self.filename, 'wb') as f:
            f.write(idsString)
