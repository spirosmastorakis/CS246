import scrapy
from util import find_nth

def parseClassAttributes(quarters):
    filename = "results/classes.txt"
    f = open(filename)
    urls = []
    for quarter in enumerate(quarters):
        f = open(filename)
        for myId in f:
            urls.append("http://www.registrar.ucla.edu/schedule/" + "crsredir.aspx?termsel=" + quarter[1] + "&subareasel=" + myId)
    return urls


class getClassesOverall(scrapy.Spider):
    name = "getClassesOverall"
    allowed_domains = ["registrar.ucla.edu"]
    start_urls = []
    filename = "results/classesAllDepts"
    quarters = []
    files = []

    def __init__(self, quarters = ["16S"]):
        if (type(quarters) is not list):
            newquarters = quarters.replace(",", " ")
            quartersList = []
            for quarter in newquarters.split():
                quartersList.append(quarter)
                quarters = quartersList
        self.quarters = quarters
        for quarter in quarters:
            thisFileName = self.filename + "-" + quarter + ".txt"
            f = open(thisFileName, 'wb')
            self.files.append(f)
        self.start_urls = parseClassAttributes(quarters)
        #parseClassAttributes(quarters)


    def parse(self, response):
        myIndex1 = find_nth(response.url, "=", 1)
        myIndex2 = find_nth(response.url, "&", 1)
        quarter = response.url[myIndex1 + 1:myIndex2]
        resp = response.xpath('//select[@id="ctl00_BodyContentPlaceHolder_crsredir1_lstCourseNormal"]/option').extract()
        classes = []
        for myclass in enumerate(resp):
            endingIndex = find_nth(myclass[1], '"', 2);
            classes.append(myclass[1][15:endingIndex])
        for index, myId in enumerate(classes):
            classes[index] = myId.replace(" ", "+")
        classesString = "\n".join(classes)
        classesString2 = classesString
        if classesString2.strip():
            index = self.quarters.index(quarter)
            self.files[index].write(classesString + "\n")
