#!/usr/bin/python

import os, csv

with open('classes.csv', 'rb') as f:
    with open('classes-new.csv', 'wb') as f1:
        reader = csv.reader(f)
        writer = csv.writer(f1)
        for row in reader:
            if row[10]:
                writer.writerow(row)
