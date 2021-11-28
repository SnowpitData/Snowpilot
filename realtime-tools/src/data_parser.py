# -*- coding: utf-8 -*-
"""
Created on Thu Jul 29 12:41:53 2021

@author: Ron Simenhois
"""
import os
import numpy as np
from datetime import datetime
import xml.etree.ElementTree as ET
from config import set_logger, LOG_DIR




def inbtween(a,b,c):
    return a <= c <= b or b <= c <= a

def get_date(pit):
    '''
    A utility function to retrive the date where the snowpit was dug 

    Parameters
    ----------
    pit : xml.etree.ElementTree.Element
        An xml tree element that contains the pit data.

    Returns
    -------
    date : str
        date string.

    '''
    TIME_STAMP_LEN = 10
    t = int(pit.attrib['timestamp'][:TIME_STAMP_LEN])
    date = datetime.fromtimestamp(t).strftime('%m/%d/%Y')
    
    return date
    

def get_ect_results(pit):
    '''
    A utility function to retrive the least stable ECT result in the pit

    Parameters
    ----------
    pit : xml.etree.ElementTree.Element
        An xml tree element that contains the pit data.

    Returns
    -------
    score : str
        ECTP, ECTN, ECTX or No ECT.
    ecScore : int
        The number of taps if the secore is ECTP or ECTN.
    color
        The color code for the ECT result.

    '''
    ECT_score = {'ECTPV': 0, 'ECTP': 1, 'ECTN': 2, 'ECTX': 3, '': 4}
    ECT_score_grade = {'ECTPV':  'poor', 'ECTP1':  'poor', 'ECTP2':  'poor',
                       'ECTP3':  'poor', 'ECTP4':  'poor', 'ECTP5':  'poor',
                       'ECTP6':  'poor', 'ECTP7':  'poor', 'ECTP8':  'poor',
                       'ECTP9':  'poor', 'ECTP10': 'poor', 'ECTP11': 'poor',
                       'ECTP12': 'poor', 'ECTP13': 'poor', 'ECTP14': 'poor',
                       'ECTP15': 'poor', 'ECTP16': 'poor', 'ECTP17': 'poor',
                       'ECTP18': 'poor', 'ECTP19': 'poor', 'ECTP20': 'poor',
                       'ECTP21': 'poor', 'ECTP22': 'poor', 'ECTP23': 'poor',
                       'ECTP24': 'fair', 'ECTP25': 'fair', 'ECTP26': 'fair',
                       'ECTP27': 'fair', 'ECTP28': 'fair', 'ECTP29': 'fair',
                       'ECTN1':  'fair', 'ECTN2':  'fair', 'ECTN3':  'fair',
                       'ECTN4':  'fair', 'ECTN5':  'fair', 'ECTN6':  'fair',
                       'ECTN7':  'fair', 'ECTN8':  'fair', 'ECTN9':  'fair',
                       'ECTN10': 'good', 'ECTN11': 'good', 'ECTN12': 'good',
                       'ECTN13': 'good', 'ECTN14': 'good', 'ECTN15': 'good',
                       'ECTN16': 'good', 'ECTN17': 'good', 'ECTN18': 'good',
                       'ECTN19': 'good', 'ECTN20': 'good', 'ECTN21': 'good',
                       'ECTN22': 'good', 'ECTN23': 'good', 'ECTN24': 'good',
                       'ECTN25': 'good', 'ECTN26': 'good', 'ECTN27': 'good',
                       'ECTN28': 'good', 'ECTN29': 'good', 'ECTN30': 'good',
                       'ECTX':   'good', np.infty: 'no test'}
    
    ECT_res2color = {'good':         'green',
                     'fair':         'yellow',
                     'poor':         'red',
                     'no test':      'gray',
                     'N/A':          'gray'}
        
    score = ''
    ecScore = np.infty
    ecScores = []
    for t in pit.findall('Shear_Test_Result'):
        if t.attrib['score'] in ECT_score:
            if ECT_score[t.attrib['score']] <= ECT_score[score]:
                if ECT_score[t.attrib['score']] < ECT_score[score]:
                   ecScores = [] 
                score = t.attrib['score']
                if t.attrib['ecScore'] != '':
                    ecScores.append(int(t.attrib['ecScore']))
    try:
        ecScore = min(ecScores)
    except ValueError:
        ecScore = np.infty
    ect2stability = ECT_score_grade.get(f'{score}{ecScore if ecScore <= 31 else ""}', 'N/A')
    return score, ecScore, ECT_res2color[ect2stability]


def get_pst_results(pit):
    '''
    A utility function to retrive the least stable ECT result in the pit

    Parameters
    ----------
    pit : xml.etree.ElementTree.Element
        An xml tree element that contains the pit data.

    Returns
    -------
    str
        PST result code.

    '''
    PST_score = {'End': 0, 'Arr': 1, 'SF': 2, ' ': 3}
    score = ' '
    cutLen = np.infty
    for t in pit.findall('Shear_Test_Result'):
        if t.attrib['code']=='PST':
            if PST_score[t.attrib['score']] <= PST_score[score]:
                score = t.attrib['score']
                if t.attrib['lengthOfCut'] != '':
                    cutLen = min(float(t.attrib['lengthOfCut']), cutLen)
                    
    return score[0] + str(cutLen).replace('inf', '').replace('.0', '')


def count_pit_lemons(pit):
    '''
    A utility function to calculates and retuns the number of lemons in the pit.
    Returns "N/A" if the pit data is  insufficient to calculate 
    the number of lemons

    Parameters
    ----------
    pit : xml.etree.ElementTree.Element
        An xml tree element that contains the pit data.

    Returns
    -------
    color: str
        Color code for the number of lemons, from geen (0 - 2) to red (4 & 5).
    number of lemons str, int 
        "N/A" or # of lemons.
    '''
    
    hrd = ['F-', 'F', 'F+', '4F-', '4F', '4F-', '4F+', '1F-', '1F', 
           '1F+', 'P-', 'P', 'P+', 'K-', 'K', 'K+', 'I-', 'I', 'I+']
    idx_hrd = [0.7, 1. , 1.3, 1.7, 2. , 2.3, 2.7, 3. , 3.3, 3.7, 4. , 
               4.3, 4.7, 5. , 5.3, 5.7, 6. , 6.3, 6.7, 7. , 7.3]
    lemons2color = {'N/A': 'gray', 0: 'green', 1: 'green', 
                    2: 'green', 3: 'yellow', 4: 'red', 5: 'red',}
    
    hrd2idx = dict(zip(hrd, idx_hrd))
    attributes = ['startDepth', 'endDepth', 'grainType', 'hardness1','grainSize']
    layers = []
    for l in pit.findall('Layer'):
        # Make sure all the parameters we need exist, if not return "N/A"
        if not np.all([l.attrib[att]!='' for att in attributes]):
            return 'gray', 'N/A'
        l_vals = {}
        l_vals['depth_v'] = min(float(l.attrib['startDepth']), float(l.attrib['endDepth'])) < 100
        l_vals['thickness_v'] = abs(float(l.attrib['startDepth']) - float(l.attrib['endDepth'])) < 10
        l_vals['wl_type_v'] = (l.attrib['grainType'][:2] in ['DH', 'FC', 'SH'])
        l_vals['hardness'] = hrd2idx[l.attrib['hardness1']]            
        l_vals['gr_size'] = float(l.attrib['grainSize'])
        layers.append(l_vals)
    for lb, lt in zip(layers, layers[1:]):
        lb['hardness_v'] = abs(lt['hardness'] - lb['hardness']) > 1
        lb['gr_size_v'] = abs(lt['gr_size'] - lt['gr_size']) > 1
    try:
        lemons = max([sum([int(v) for k, v in l.items() if k.endswith('_v')]) for l in layers])
    except ValueError:
        lemons = 'N/A'
    color =  lemons2color[lemons]   
        
    return color, lemons


def find_LOC(pit,
             min_loc_depth=20):
    '''
    A utility function tp find the most likely layer of concern in a snowpit 

    Parameters
    ----------
    pit : xml.etree.ElementTree.Element
        An xml tree element that contains the pit data..
    min_loc_depth : int, optional
        The minimum depth of the layer of concern. The default is 20.

    Returns
    -------
    deoth: int
        the depth of the layer of concern.

    '''
    
    def calc_loc_depth(pit,
                       snow_depth):
        measure_from = pit.attrib['measureFrom']
        loc_depth = int(pit.attrib['iDepth']) 
        if measure_from == 'bottom':
            loc_depth = snow_depth - loc_depth 
            
        return loc_depth
        
    
    def min_ect(pit):
        taps, depth = np.infty, None
        for t in pit.findall('Shear_Test_Result'):
            if t.attrib['score']=='ECTP':
                if float(t.attrib['ecScore']) < taps:
                    taps = float(t.attrib['ecScore'])
                    depth = float(t.attrib['sdepth'])
        return depth

        
    def min_pst(pit):
        cut, depth = np.infty, None
        for t in pit.findall('Shear_Test_Result'):
            if t.attrib['score']=='End':
                test_cut = float(t.attrib['lengthOfCut']) / float(t.attrib['lengthOfColumn'])
                if test_cut < cut:
                    cut = test_cut
                    depth = float(t.attrib['sdepth'])
        return depth
    
    
    def min_ct(pit):
        taps, depth = np.infty, None
        for t in pit.findall('Shear_Test_Result'):
            if t.attrib['code']=='CT':
                if float(t.attrib['ctScore']) < taps:
                    taps = float(t.attrib['ctScore'])
                    depth = float(t.attrib['sdepth'])
        return depth
        
    try:
        calc_depth = max([int(l.attrib['startDepth']) for l in pit.findall('Layer')])
    except ValueError:
        calc_depth = -1
    snow_depth = max(calc_depth, int(pit.attrib['heightOfSnowpack']))
    try:
        loc_depth = calc_loc_depth(pit=pit, 
                                   snow_depth=snow_depth)  
    except ValueError: 
        try:
            depth = min_ect(pit)
            if depth is None:
                depth = min_pst(pit)
                if depth is None:
                    depth = min_ct(pit)
            loc_depth = depth if pit.attrib['measureFrom']=='top' else snow_depth - depth
        except TypeError:
             loc_depth = None
    return loc_depth
    

def get_activity(pit):
    '''
    A utility function to extract signs of instability that associated with 
    the snowpit

    Parameters
    ----------
    pit : xml.etree.ElementTree.Element
        An xml tree element that contains the pit data.

    Returns
    -------
    activity_color : str
        color name to plot dots on the map.
    activities : str
        a string of all the signs of instability that are associated with 
    the snowpit.

    '''
    
    activity_rating = {'': -1,
                       'We snowmobiled slope': 0,
                       'We skied slope': 1,
                       'Snowmobile tracks on slope': 2,
                       'Ski tracks on slope': 3,
                       'Collapsing, localized': 4,
                       'Cracking': 5,
                       'Collapsing, widespread': 6,
                       'Recent avalanche activity on different slopes': 7,
                       'Recent avalanche activity on similar slopes': 8,
                       }
    activity_colors = {-1: 'white',
                       0: 'lightgreen',
                       1: 'lightgreen',
                       2: 'darkgreen',
                       3: 'darkgreen',
                       4: 'yellow',
                       5: 'orange',
                       6: 'purple',
                       7: 'red',
                       8: 'red'
                      }

        
    activities = [a.strip() for a in pit.attrib['activities'].split(';')] 
    activity_id = max([activity_rating.get(act, -1) for act in activities]) 
    activity_color = activity_colors[activity_id]
    activities = ', '.join(activities) if activity_id != -1 else 'N/A'
    return activity_color, activities
        

def parse_pits_data(xml,
                    **kwargs): 
    '''
    This function parse an xml string with snowpits data into a list of dicts.
    Each dict contains ploting infomation of a pit to plot on a folium map. 

    Parameters
    ----------
    xml : str
        a string with snowpits date in xml format.
    **kwargs : dict
        DESCRIPTION.

    Returns
    -------
    data : list of dicts
        DESCRIPTION:
            {
             "lat" - type float,
             "long" - type: float,
             "nid" - pit id, type: int,
             "text" - type: str,
             "dot_param" - type: str, color name,
             "line_param" - type: str, color name,
             "tooltip" - type: str
             }.
    '''    

    text_param = kwargs.get('text_param', 'ECT')
    dot_color_param = kwargs.get('dot_color_param', 'ECT')
    line_color_param = kwargs.get('line_color_param', 'HS')
    observers = kwargs.get('observer_group', 'All')
    data = []
    log.info('Paese xml data with: {}'.format(kwargs))
    try:
        root = ET.fromstring(xml)
    
        log.debug('Total # of pits: {}'.format(len(root.findall('Pit_Observation'))))
        for p in root.findall('Pit_Observation'):
            try:
                if observers=='Pros':
                    if not p.find('User').attrib['prof']:
                        continue
                if observers=='CAIC':
                    if p.find('User').attrib['affil'] != 'Colorado Avalanche Information Center':
                        continue
                if p.attrib['longitude']!='' and p.attrib['lat']!='':
                    long = float(p.attrib['longitude'])
                    lat = float(p.attrib['lat'])
                    nid = int(p.attrib['nid'])
                else:
                    continue
                # Get text values
                text = ''
                line_param = None 
                dot_param = None
                # Set marker text
                if text_param=='ECT':
                    score, _, _ = get_ect_results(p)
                    text = score[3:]
                elif text_param=='CT':
                    ctScore = np.infty
                    for t in p.findall('Shear_Test_Result'):
                        if t.attrib['code']=='CT':
                            ctScore = min(ctScore, int(t.attrib['ctScore']))
                    text = str(ctScore).replace('inf', '')
                elif text_param=='PST':
                    text = get_pst_results(p)  
                    
                # Set marker color:  
                tooltip_lst = ['Date: {}'.format(get_date(p))]
                
                # Snow height
                if dot_color_param=='HS':
                    dot_param =  float(p.attrib['heightOfSnowpack'])
                if line_color_param=='HS':
                    line_param = float(p.attrib['heightOfSnowpack'])
                tooltip_lst.append(f'Hight of snow: {int(p.attrib["heightOfSnowpack"])} cm')
                
                # ECT:
                score, taps, color = get_ect_results(p)
                tooltip_lst.append(f'ECT results: {score}{taps if taps <=30 else ""}')
                if dot_color_param=='ECT':
                    dot_param = color
                if line_color_param=='ECT':
                    line_param = color
                
                # LEMONS:
                color, lemon_count = count_pit_lemons(p)
                tooltip_lst.append(f'Number of lemons: {lemon_count}')
                if dot_color_param=='LEMONS':
                    dot_param = color
                if line_color_param=='LEMONS':
                    line_param = color
                
                # signs of instability    
                color, tooltip = get_activity(p)
                tooltip_lst.append('Signs of instability: ' + tooltip)
                if dot_color_param == 'ACTIVITY':
                    dot_param = color
                if line_color_param == 'ACTIVITY':
                    line_param = color
                tooltip = '<br>'.join(tooltip_lst)    
            except:
                continue
            data.append(dict(lat=lat, long=long, nid=nid,
                     text=text, dot_param=dot_param,
                     line_param=line_param,
                     tooltip=tooltip))
    except:
            pass
    log.debug('Total # of pits to map: {}'.format(len(data)))
    return data

log = set_logger('Parse Data', 
                 log_file = os.path.join(LOG_DIR, 'SnowPilotOnMap.log'),
                 level='DEBUG')
