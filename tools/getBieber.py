import sys
import urllib
from cStringIO import StringIO
from pdfminer.pdfinterp import PDFResourceManager, process_pdf
from pdfminer.pdfdevice import PDFDevice
from pdfminer.converter import TextConverter
import re


TEMPLATE = """  {
        "company": "Bieber",
        "departureTime": "%s",
        "departureLocation": "%s",
        "arrivalTime": "%s",
        "arrivalLocation": "%s"
    },
"""

def pdf2txt(buff):
    txt = StringIO()
    rsrcmgr = PDFResourceManager(caching=True)
    device = TextConverter(rsrcmgr, txt, codec='utf-8', laparams=None)
    process_pdf(rsrcmgr, device, buff, set(), maxpages=0, password='', caching=True, check_extractable=True)
    return txt.getvalue()

def brittle_extractor(blob, from_city, to_city):
    """ brittle_extractor -- A Brittle extractor, it's pretty becuase it depends almost entirely
                             on the format of the PDF not changing, but hey it's better than typing
                             all this shit by hand.
        Arguments:
        from_city -- name of depature city (e.g. Hellertown)
        to_city -- name of destination city (e.g. New York City)
    """
    blob = blob.lower()
    scheduleNumbers = re.findall("SCHEDULE NUMBER(([\d]{3})+)",txt)
    scheduleNumbers = re.findall('(\d\d\d)', scheduleNumbers[0][0])
    week = [x for x in scheduleNumbers if x[0]=='1']
    weekend = [x for x in scheduleNumbers if x[0]=='2']
    #print len(scheduleNumbers), "schedules"
    #print len(week), 'week day'
    #print len(weekend), 'week end'

    groups = re.findall('([^0-9]+)((\d{3,4}[ap])+)',blob)
    src = [] #3 6
    dst = [] #4 5
    for i in xrange(len(groups)):
        if from_city.lower() in groups[i][0]:
            src.append(i)
        if to_city.lower() in groups[i][0]:
            dst.append(i)
    
    if src[0] < dst[0]:
        a,b = groups[src[0]], groups[dst[0]]
    else:
        a,b = groups[src[1]], groups[dst[1]]
    
    a,b = re.findall('(\d{3,4}[ap])',a[1]), re.findall('(\d{3,4}[ap])',b[1])
    n = len(week)
    for lv,ar in zip(a,b)[:n]:
        print TEMPLATE%(lv,from_city,ar,to_city)
    


if __name__ == '__main__':
    if len(sys.argv) != 2:
        print "usage: python getBieber.py http://url/to/schedule.pdf > bieber.json"
        print "       eg, http://www.biebertourways.com/active/2013-2-26/NYC%203-2013.pdf"
    else:
        pdf = StringIO(urllib.urlopen(sys.argv[1]).read())
        txt = pdf2txt(pdf)
        txt = txt.decode('ascii','ignore')
        s = brittle_extractor(txt, 'Hellertown', 'PABT')
        s = brittle_extractor(txt, 'PABT', 'Hellertown')
