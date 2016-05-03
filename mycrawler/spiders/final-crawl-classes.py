import scrapy
from util import find_nth
import os
import csv

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
    #urls.append("http://www.registrar.ucla.edu/schedule/detselect.aspx?termsel=16S&subareasel=ART&amp;ARC%0A+ST&idxcrs=0283++C+%0A")
    return urls


class finalCrawlClasses(scrapy.Spider):
    name = "finalCrawlClasses"
    allowed_domains = ["registrar.ucla.edu"]
    start_urls = []
    filenames = ["classes.csv", "discussion-sections.csv"]
    files = []

    def __init__(self):
        self.start_urls = parseClassAttributes()
        # Open the files
        f1 = open(self.filenames[0], 'wb')
        self.files.append(f1)
        f2 = open(self.filenames[1], 'wb')
        self.files.append(f2)

    def parse(self, response):
        termAndYear = "ctl00_BodyContentPlaceHolder_detselect_lblTermHeader"
        dept = "ctl00_BodyContentPlaceHolder_detselect_lblCourseHeader"

        attrs = ["dgdClassDataColumnIDNumber", "dgdClassDataActType", "dgdClassDataDays", "dgdClassDataTimeStart",
        "dgdClassDataTimeEnd", "dgdClassDataBuilding", "dgdClassDataRoom", "dgdClassDataEnrollTotal",
        "dgdClassDataEnrollTotal", "dgdClassDataEnrollCap", "dgdClassDataWaitListTotal", "dgdClassDataWaitListCap",
        "dgdClassDataStatus"]
        resultClasses = []
        resultDiscussionSections = []
        tempRes = []
        '''
        # Parse the attributes of the response
        for attr in attrs:
            resp = response.xpath('//td[re:test(@class, "' + str(attr) + '")]//span//text()').extract_first()
            resultClasses.append(resp)
        '''
        for attr in attrs:
            tempRes.append(response.xpath('//td[re:test(@class, "' + str(attr) + '")]//span//text()').extract())

        #print tempRes

        listsize = len(tempRes)
        elementsize = len(tempRes[0])
        for index in range(0, elementsize):
            isDisc = False
            isLec = False
            mylistdisc = []
            mylistclass = []
            #print tempRes[1][index]
            if tempRes[1][index].strip() == "DIS":
                isDisc = True
            elif tempRes[1][index].strip() == "TBA" or tempRes[1][index].strip() == "UNSCHED" or tempRes[1][index].strip() == "TUT":
                continue
            elif tempRes[2][index].strip() == "VAR" or tempRes[2][index].strip() == "UNSCHED" or tempRes[2][index].strip() == "TBA":
                continue
            else:
                isLec = True
            for index2 in range(0, listsize - 1):
                #print tempRes[index2][index]
                if index2 == 0:
                    if tempRes[index2][index].strip() == "Crs Info":
                        temp = tempRes[index2][index + 1].strip()
                        tempInt = int(temp)
                        tempInt = tempInt - 1
                        tempRes[index2][index] = tempInt
                if isLec:
                    if not tempRes[index2]:
                        mylistclass.append("")
                    else:
                        mylistclass.append(tempRes[index2][index].strip())
                if isDisc:
                    if not tempRes[index2]:
                        mylistdisc.append("")
                    else:
                        mylistdisc.append(tempRes[index2][index].strip())

            if not tempRes[listsize - 1][index].strip():
                if isLec:
                    mylistclass.append(tempRes[listsize - 1][index + 1].strip())
                else:
                    mylistdisc.append(tempRes[listsize - 1][index + 1].strip())
            else:
                if isLec:
                    mylistclass.append(tempRes[listsize - 1][index].strip())
                else:
                    mylistdisc.append(tempRes[listsize - 1][index].strip())

            if isLec:
                #print mylistclass
                resultClasses.append(mylistclass)
            else:
                #print mylistdisc
                resultDiscussionSections.append(mylistdisc)

        # Write the data to the right output file
        self.writeData(self.files[0], resultClasses)
        self.writeData(self.files[1], resultDiscussionSections)

    def writeData(self, myFile, result):
        csvwriter = csv.writer(myFile, delimiter=',')
        #print data
        for line in result:
            csvwriter.writerow(line)
