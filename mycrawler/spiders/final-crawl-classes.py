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
    #urls.append("http://www.registrar.ucla.edu/schedule/detselect.aspx?termsel=16S&subareasel=COM+SCI&idxcrs=0111++++")
    return urls


class finalCrawlClasses(scrapy.Spider):
    name = "finalCrawlClasses"
    allowed_domains = ["registrar.ucla.edu"]
    start_urls = []
    filenames = ["classes.csv", "discussion-sections.csv"]
    files = []
    counterClass = 0
    counterDisc = 0

    def __init__(self):
        self.start_urls = parseClassAttributes()
        # Open the files
        f1 = open(self.filenames[0], 'wb')
        self.files.append(f1)
        f2 = open(self.filenames[1], 'wb')
        self.files.append(f2)

    def parse(self, response):
        quarterAndYear = response.xpath('//span[re:test(@class, "heading2")]//text()').extract_first()
        quarter = ""
        year = ""
        doneWithQuarter = False
        for char in quarterAndYear:
            if quarterAndYear == True:
                year += char
            elif char.isspace():
                quarterAndYear = True
            else:
                quarter += char

        #print quarterAndYear
        lecInfo = response.xpath('//span[re:test(@class, "coursehead")]//text()').extract()
        dept = lecInfo[0].strip()
        #print dept
        lectures = lecInfo[2:]
        #print lectures

        classInfo = response.xpath('//table[re:test(@class, "dgdTemplateGrid")]//text()').extract()
        #print classInfo
        classInstructors = response.xpath('//span[re:test(@class, "fachead")]//text()').extract()
        #print classInstructors
        entireClassName = classInfo[3].strip()
        cnum = ""
        cnumFound = False
        cnumIndex = 0
        for char in entireClassName:
            cnumIndex = cnumIndex + 1
            if char.isspace() and cnumFound == False:
                cnum = ""
            elif char.isdigit():
                cnum += char
                cnumFound = True
            elif char.isspace() and cnumFound == True:
                break
            else:
                cnum += char

        classTitle = entireClassName[cnumIndex: ].strip()

        classTitle2 = response.xpath('//span[re:test(@class, "heading5")]//text()').extract()

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

        #tempRes = filterCancelledSections(tempRes)

        listsize = len(tempRes)
        #print tempRes
        elementsize = len(tempRes[0])
        encounteredLec = False
        for index in range(0, elementsize):
            isDisc = False
            isLec = False
            mylistdisc = []
            mylistclass = []
            #print tempRes[1][index]
            if tempRes[1][index].strip() == "DIS":
                #print "here"
                isDisc = True
            elif tempRes[1][index].strip() == "TBA" or tempRes[1][index].strip() == "UNSCHED" or tempRes[1][index].strip() == "TUT":
                continue
            elif tempRes[2][index].strip() == "VAR" or tempRes[2][index].strip() == "UNSCHED" or tempRes[2][index].strip() == "TBA":
                continue
            elif tempRes[1][index].strip() == "LAB" and encounteredLec == True:
                #print "disc"
                isDisc = True
            else:
                #print "lec"
                #print tempRes[1][index].strip()
                encounteredLec = True
                isLec = True

            classCancelled = False
            if tempRes[listsize - 1][index].strip() == "Cancelled" or tempRes[listsize - 1][index + 1].strip() == "Cancelled":
                classCancelled = True

            for index2 in range(0, listsize - 1):
                #print tempRes[index2][index]
                #print tempRes[5][3]
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
                        try:
                            if isinstance(tempRes[index2][index], int):
                                mylistclass.append(tempRes[index2][index])
                            else:
                                mylistclass.append(tempRes[index2][index].strip())
                        except IndexError:
                            mylistclass.append("")
                elif isDisc:
                    if not tempRes[index2]:
                        mylistdisc.append("")
                    else:
                        try:
                            if isinstance(tempRes[index2][index], int):
                                mylistdisc.append(tempRes[index2][index])
                            else:
                                mylistdisc.append(tempRes[index2][index].strip())
                        except IndexError:
                            mylistdisc.append("")

                if index2 == 0:
                    if isLec:
                        mylistclass.append(quarter.strip())
                        mylistclass.append(year.strip())
                        mylistclass.append(dept.strip())
                        mylistclass.append(cnum.strip())
                        #print classTitle2[2 * index + 1]
                        classTitleCopy = classTitle
                        if len(classTitle2) > 0:
                            copy = classTitle2[2 * index + 1]
                            classTitleCopy = classTitle + " : " + copy
                            classTitleCopy = classTitleCopy.encode('ascii', 'ignore')
                        mylistclass.append(classTitleCopy)
                        if classCancelled == True:
                            #tempRes = myremove(tempRes, index2, index)
                            mylistclass.append("")
                        else:
                            try:
                                instructorName = ''.join(classInstructors[index].split())
                                mylistclass.append(instructorName)
                            except IndexError:
                                mylistclass.append("")
                    if isDisc:
                        mylistdisc.append(quarter.strip())
                        mylistdisc.append(year.strip())
                        mylistdisc.append(dept.strip())
                        mylistdisc.append(cnum.strip())

            if not tempRes[listsize - 1][index].strip():
                if isLec:
                    mylistclass.append(tempRes[listsize - 1][index + 1].strip())
                if isDisc:
                    mylistdisc.append(tempRes[listsize - 1][index + 1].strip())
            else:
                if isLec:
                    mylistclass.append(tempRes[listsize - 1][index].strip())
                if isDisc:
                    mylistdisc.append(tempRes[listsize - 1][index].strip())

            if isLec:
                #print mylistclass
                mylistclass = [self.counterClass] + mylistclass
                self.counterClass = self.counterClass + 1
                resultClasses.append(mylistclass)
            if isDisc:
                #print mylistdisc
                mylistdisc = [self.counterDisc] + mylistdisc
                self.counterDisc = self.counterDisc + 1
                resultDiscussionSections.append(mylistdisc)

        # Write the data to the right output file
        self.writeData(self.files[0], resultClasses)
        self.writeData(self.files[1], resultDiscussionSections)

    def writeData(self, myFile, result):
        csvwriter = csv.writer(myFile, delimiter=',')
        #print data
        for line in result:
            csvwriter.writerow(line)

def myremove(result, row, column):
    resultCopy = result
    counter = 0
    for line in result:
        #print result[counter]
        try:
            line.pop(column)
        except IndexError:
            counter = counter + 1
            continue
        counter = counter + 1
    return result

def filterCancelledSections(result):
    row = 0
    column = 0
    print result
    resultCopy = result
    for sublist in result:
        column = 0
        for element in sublist:
            if element.strip() == "Cancelled":
                resultCopy = myremove(resultCopy, row, column)
            else:
                column = column + 1
        row = row + 1
    print resultCopy
    return resultCopy
