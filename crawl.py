#!/usr/bin/python

import os

academicYears = ["00", "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16"]
quarters = ["F", "W", "S"]

for year in enumerate(academicYears):
    for quarter in enumerate(quarters):
        os.system("scrapy crawl getClassesPerDept -a quarters=" + year[1]+quarter[1])

os.system("scrapy crawl finalCrawlClasses")
