import scrapy
from util import find_nth

def parseClassAttributes():
    quarters = ["16S"]
    filename = "classes.txt"
    f = open(filename)
    urls = []
    for quarter in enumerate(quarters):
        for myId in f:
            urls.append("http://www.registrar.ucla.edu/schedule/" + "crsredir.aspx?termsel=" + quarter[1] + "&subareasel=" + myId)
    return urls


class getClassesPerDeptSpider(scrapy.Spider):
    name = "getClassesPerDept"
    allowed_domains = ["registrar.ucla.edu"]
    start_urls = parseClassAttributes()
    filename = "classesPerDept.txt"

    def parse(self, response):
        test = response.xpath('//select[@id="ctl00_BodyContentPlaceHolder_crsredir1_lstCourseNormal"]/option').extract()
        classes = []
        for myclass in enumerate(test):
            endingIndex = find_nth(myclass[1], '"', 2);
            classes.append(myclass[1][15:endingIndex])
        for index, myId in enumerate(classes):
            classes[index] = myId.replace(" ", "+")
        idsString = "\n".join(classes)
        print idsString
        with open(self.filename, 'wb') as f:
            f.write(idsString)
