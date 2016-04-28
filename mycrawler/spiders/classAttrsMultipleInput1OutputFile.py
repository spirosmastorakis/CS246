import scrapy
from util import find_nth
import os

def parseClassAttributes():
    directory = os.path.join('results')
    filenames = []
    urls = []
    for myFile in os.listdir(directory):
        #print myFile
        if "classesPerDept" in myFile:
            filenames.append(myFile)
    if not filenames:
        raise ValueError('No files found')
    for filename in enumerate(filenames):
        index1 = find_nth(filename[1], "-", 1);
        index2 = find_nth(filename[1], "-", 2);
        quarter = filename[1][index1 + 1: index2]
        index3 = find_nth(filename[1], ".", 1)
        deptId = filename[1][index2 + 1: index3]
        f = open("results/" + filename[1], "r")
        for classId in f:
            url = "http://www.registrar.ucla.edu/schedule/" + "detselect.aspx?termsel=" + quarter + "&subareasel=" + str(deptId) + "+ST&idxcrs=" + str(classId)
            #print url
            urls.append(url)
    return urls


class classAttrsMultipleInput1OutputFile(scrapy.Spider):
    name = "classAttrsMultipleInput1OutputFile"
    allowed_domains = ["registrar.ucla.edu"]
    start_urls = []
    filenames = []

    def __init__(self):
        self.start_urls = parseClassAttributes()

    def parse(self, response):
        attrs = ["dgdClassDataDays", "dgdClassDataTimeStart",
        "dgdClassDataTimeEnd"]
        crawlingResult = []
        for attr in attrs:
            resp = response.xpath('//td[re:test(@class, "' + str(attr) + '")]//span//text()').extract()
            crawlingResult.append(resp)
        #print crawlingResult
        indexQuarter1 = find_nth(response.url, "=", 1)
        indexQuarter2 = find_nth(response.url, "&", 1)
        quarter = response.url[indexQuarter1 + 1: indexQuarter2]
        indexDept1 = find_nth(response.url, "=", 2)
        indexDept2 = find_nth(response.url, "&", 2)
        found = False
        for filename in enumerate(self.filenames):
            #print str(filename[1])
            #print quarter in str(filename[1])
            #print dept in str(filename[1])
            #print "classAttrs" in str(filename[1])
            thisFileName = str(filename[1])
            if quarter in thisFileName and "classAttrsAll" in thisFileName:
                found = True
        name = "results/classAttrsAll-" + quarter + ".txt"
        if not found:
            #print "opening"
            f = open(name, "wr")
            self.filenames.append(f)
            self.writeData(f, crawlingResult)
        else:
            self.writeData(self.filenames[0], crawlingResult)

    def writeData(self, myFile, result):
        for element in result:
            for subelement in element:
                if subelement == "TBA":
                    element.remove(subelement)
                elif subelement == "UNSCHED":
                    element.remove(subelement)
                elif subelement == "VAR":
                    element.remove(subelement)
            if not element:
                return
        size = len(result[0])
        for index in range(0, size):
            data = ""
            for element in result:
                curr_element = element[index]
                data += curr_element.strip()
                if element != result[len(result) - 1]:
                    data += ","
            data += "\n"
            #print data
            myFile.write(data)
