'''

import scrapy
from util import find_nth
import os

def parseClassAttributes():
    quarters = ["16S"]
    directory = os.path.join('results')
    filenames = []
    for myFile in directory:
        if "classesPerDept" in myFile:
            filenames.append()
        f = open(filename)
    for quarter in enumerate(quarters):
        for myId in f:
            urls.append("http://www.registrar.ucla.edu/schedule/" + "crsredir.aspx?termsel=" + quarter[1] + "&subareasel=" + myId + "+ST&idxcrs=" + classId)
    return urls


class getClassesOverall(scrapy.Spider):
    name = "getClassesOverall"
    allowed_domains = ["registrar.ucla.edu"]
    start_urls = parseClassAttributes()
    filename = "results/classesPerDept.txt"
    f = open(filename, 'wb')


    def parse(self, response):
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
            self.f.write(classesString + "\n")

'''
