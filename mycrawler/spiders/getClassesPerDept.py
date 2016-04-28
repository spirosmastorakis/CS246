import scrapy
from util import find_nth

def parseClassAttributes():
    quarters = ["16S"]
    filename = "results/classes.txt"
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
    filename = "results/classesPerDept"

    def parse(self, response):
        myIndex = find_nth(response.url, "=", 2)
        thisFileName = self.filename + "-" + response.url[myIndex + 1:] + ".txt"
        resp = response.xpath('//select[@id="ctl00_BodyContentPlaceHolder_crsredir1_lstCourseNormal"]/option').extract()
        classes = []
        for myclass in enumerate(resp):
            endingIndex = find_nth(myclass[1], '"', 2);
            classes.append(myclass[1][15:endingIndex])
        for index, myId in enumerate(classes):
            classes[index] = myId.replace(" ", "+")
        classesString = "\n".join(classes)
        classesString2 = classesString.strip()
        print classesString2
        if classesString2:
            with open(thisFileName, 'wb') as f:
                f.write(classesString)
