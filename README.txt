CS 246 Project
==============

To run the spiders:

  scrapy crawl <spider-name>

In the spiders folder, there is a number of spiders (all the output/input files will be in the
results folders):

1) initial.py: Crawls the all the UCLA department names. Writes them on a file called classes.txt

2) getClassesPerDept: Uses the content of classes.txt as input and crawls the UCLA classes per
department. It saves the classes of each department on a separate output txt file. To specify the
desired quarters to be crawled, just type:

    scrapy crawl getClassesPerDept -a quarters=<quarter1, quarter2,.., quarterN>

For example:

    scrapy crawl getClassesPerDept -a quarters=16W,16S

If no quarters are specified (that is one runs the spider without the -a flag), by default the classes
for each department for Spring 2016 will be crawled.

3) getClassesOverall: Similar functionality as #2, but in this case the classes are written on a single
output file

4) parseClassAttr: Reads the input files containing the classes per department (compatible with spider #2)
for now. It crawls the page of each class for each department and parses the attributes specified in the
attrs list of the parse method (just add/remove elements to this list to parse more/less attributes).
It will write the attributes on an output file on a per department basis.

5) classAttrsMultipleInput1OutputFile: Similar functionality as #4, but it outputs the parsed attributes
on a single output file for all the classes of all the departments.

You first have to run spider #1, then spider #2 and then either spider #4 or #5.
