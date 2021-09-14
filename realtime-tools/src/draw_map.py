# -*- coding: utf-8 -*-
"""
Created on Wed Jul  7 14:12:36 2021

@author: Ron Simenhois

version 20210808 - popup stay on when mouse click outside of the popup window.
"""

import numpy as np
import folium
import folium.plugins
import argparse
import os
import branca.colormap as cm
from snow_pilot_download import download_xml, load_xml_from_file
from config import (PARENT_DIR,
                    HOST_PARENT_DIR,
                    DEFAULT_MAP, 
                    LEGEND_PATH, 
                    MAPS_PATH,
                    LOG_DIR,
                    set_logger)
from avalanche_loader import get_avalanche_activity_by_zone
from data_parser import parse_pits_data



def map_avalanches(_map,
                   avalanches,
                   method='count',
                   **kwargs):
    '''
    

    Parameters
    ----------
    _map : Folium Map object
        DESCRIPTION.
    avalanches : geopandas DataFrame.
        DataFrame with columns: Zone Name and "totals" that contains avrage 
        daily average avalanche count or daily average AAI for each zone.
    method : str, optional
        count for average daily count or AAI for average daily AAI. 
        The default is 'count'.
    **kwargs : dict
        Contain staer and end dates for colorbar title.

    Returns
    -------
    _map : Folium Map object
        The recived map with additional layer that present the avalanche
        activity for each zone.
    '''
    
    start_date = kwargs['start_date']
    end_date = kwargs['end_date']
    method = 'count' if  kwargs['show_avs'] == 'Daily count mean' else 'AAI' 
    #colors = ['green', 'yellow', 'orange', 'red', 'black']

    
    if method == 'count':
        colors = ['white', 'black']
        min_val, max_val = 0, 15
        index = np.linspace(min_val, max_val, len(colors)).astype(int).tolist()
        cm_title = 'Mean daily avalanche count {} to {}'.format(start_date,
                                                                end_date)
        tiptool_title = 'Mean daily avalanche count: '
        
    else: # AAI
        colors = [(255,255,255,255), 
                  (225,225,225,255), 
                  (150,150,150,150), 
                  (50,50,50,255), 
                  (0,0,0,255)]
        min_val, max_val = 0, 88 # One D4 or 10 D3
        index = [ 0, 0.21, 1 , 21, 88] # Taken from here: https://www.researchgate.net/publication/339887474_On_the_relation_between_avalanche_occurrence_and_avalanche_danger_level
        cm_title = 'Mean daily AAI {} to {}'.format(start_date,
                                                    end_date)
        tiptool_title = 'Mean daily AAI: '
    

    
    colormap = cm.LinearColormap(colors=colors,
                                 index=index,
                                 vmin=min_val, vmax=max_val,
                                 caption=cm_title)
    #m = folium.Map(location=location, zoom_start=7, tiles="Stamen Terrain")
    
    style_function = lambda x: {"weight":0.4, 
                                'color':'black',
                                'fillColor':colormap(x['properties']['totals']),
                                'fillOpacity':0.5}
    
    highlight_function = lambda x: {'fillColor': '#000000', 
                                    'color':'#000000', 
                                    'fillOpacity': 0.50, 
                                    'weight': 0.1}
    
    avls = folium.FeatureGroup(name='Avalanche activity', show=True)
    _map.add_child(avls)
    
    _ = folium.features.GeoJson(avalanches,
                                style_function=style_function, 
                                control=False,
                                highlight_function=highlight_function, 
                                tooltip=folium.features.GeoJsonTooltip(
                                fields=['zone_name','totals', 'description'],
                                aliases=['Zone: ',tiptool_title, 'Distribution:'],
                                style=("background-color: white; color: #333333; font-family: arial; font-size: 12px; padding: 10px;") 
                                )
    ).add_to(avls)
    colormap.add_to(_map)
    
    folium.LayerControl().add_to(_map)
    
    return _map                                                        


def draw_map(save_file,
             data,
             **kwargs):
    '''
    THis fuction created a folium liflet map and add a circle marler for 
    every snowpit in the state and date range. It rhen save the map as 
    html a file.

    Parameters
    ----------
    save_file : str
        path to save the map as html file.
    data : list
        list of dicts. dicts keys are: lat, lon, nid, text, 
        dot_param, line_param
    **kwargs : dict
        A path to the saved html file.

    Returns
    -------
    save_file : TYPE
        DESCRIPTION.

    '''
    map_loc = {'CO': [39, -106.2],
               'MT': [45.5, -111]}
    state = kwargs['state']
    start_date = kwargs['start_date']
    end_date = kwargs['end_date']
    method = 'count' if  kwargs['show_avs'] == 'Daily count mean' else 'AAI' 
    avalanches = get_avalanche_activity_by_zone(start_date=start_date,
                                                end_date=end_date,
                                                method=method,
                                                state=state)
    if data == [] and avalanches.empty:
        save_file = state + DEFAULT_MAP
    else:
        _map = folium.Map(location=map_loc[state], 
                          zoom_start=8, 
                          tiles='Stamen Terrain')
        state = kwargs['state']
        if not avalanches.empty:
            _map = map_avalanches(_map, 
                                  avalanches, 
                                  **kwargs)

        colors = ['darkblue', 'blue', 'cyan', 'yellow', 'orange', 'red']
        bar_title_dict = {'HS': 'HS in cm', 'ACTIVITY': 'Level of activity', 'LEMONS': '# of lemons'}

        dot_param = kwargs.get('dot_color_param', None)
        line_param = kwargs.get('line_color_param', dot_param)
        if line_param == '':
            line_param = dot_param 
        #location = np.mean([[d['lat'] for d in data], [d['long'] for d in data]], axis=1)
        cm_title = None
        dot_legend, line_legend = '', ''
        if dot_param=='HS':
            max_val = max([d['dot_param'] for d in data])
            min_val = min([d['dot_param'] for d in data])
            cm_title = dot_param
            color_bar_type = 'Marker color: '
        else:
            dot_legend = dot_param
        if line_param=='HS':
            max_val = max([d['line_param'] for d in data])
            min_val = min([d['line_param'] for d in data])
            cm_title = kwargs.get('line_color_param', None)
            color_bar_type = 'Marker line color: '
        else:
            line_legend = line_param
            
        if dot_legend != '' or line_legend != '':
            legend_img = (dot_legend + '_' + line_legend + '_label.png').replace('__', '_')
            legend_img = os.path.join(HOST_PARENT_DIR, LEGEND_PATH, legend_img)
            #legend_img = os.path.join('http://localhost:8000/data/legends/', legend_img)
            if not os.path.isfile(legend_img):
                log.warning('Legend image {} does not exists'.format(legend_img))
            folium.plugins.FloatImage(legend_img,
                                      bottom=0,
                                      left=0).add_to(_map)
        
        folium.plugins.MousePosition(position='bottomright', separator=' | ', prefix="Mouse:").add_to(_map)
        if cm_title is not None:
            cm_title = color_bar_type + bar_title_dict[cm_title]
            index = np.linspace(min_val, max_val, 6).astype(int).tolist()
            colormap = cm.LinearColormap(colors=colors,
                                         index=index,
                                         vmin=min_val, vmax=max_val,
                                         caption=cm_title)
            colormap.add_to(_map)
            
        
        for p in data:
            location = [p['lat'], p['long']]
            
            if dot_param=='ECT' or dot_param=='ACTIVITY' or dot_param=='LEMONS':
                fill_color = p['dot_param']
            else:
                fill_color = colormap(float(p['dot_param']))
            if line_param=='ECT' or line_param=='ACTIVITY' or line_param=='LEMONS':
                line_color = p['line_param']
            else:

                line_val = float(p['line_param'])
                line_color = colormap(line_val)
            link = 'https://snowpilot.org/node/{}'.format(p['nid'])
            # This code set the popup with a link to opme the pit in a different window.
            # popup = """<a href="#" onClick="window.open('{}','_blank',
            #                              'resizable,height=650,width=650'); 
            #                              return false;">Click to see the full pit</a>""".format(link)
             
            # popup = folium.Popup(popup)   
            # This line opens an Iframe with the pit page inside
            popup = folium.Popup(f'<iframe src={link} width=650 height=650></iframe>')
            folium.CircleMarker(location=location,
                                radius=8,
                                fill=True,
                                color=line_color,
                                fill_color = fill_color,
                                fill_opacity=0.9,
                                tooltip=p.get('tooltip', 'N/A')
                               ).add_to(_map)
            
            # Add P, N and X depends on ECT results
            folium.Marker(location=location,
                          icon=folium.DivIcon(html=f"""<b><div style="font-family: courier new; color: black; font-size:16px">{p['text']}</div></b>""",
                                             icon_anchor=(5,11),),
                          popup=popup).add_to(_map)
            
        _map.save(os.path.join(PARENT_DIR, MAPS_PATH, save_file))
        
    return save_file    


def main(args):
    
    params = args.params
    params = params.replace('{', '').replace('}','')
    params_list = [x for x in params.strip().split(',')]
    params_list = [x.replace('"','') for x in params_list]
    params_dict = {x.split(':')[0].replace('"','').strip(): x.split(':')[1].replace('"','').strip() for x in params_list}
    
    start_date = params_dict['start_date']
    end_date = params_dict['end_date']
    state = params_dict.get('state', 'CO')
    save_file = '&'.join([k+'='+v for k, v in params_dict.items()]) + '.html'
    save_file_path = os.path.join(PARENT_DIR, MAPS_PATH, save_file)
    if not os.path.isfile(save_file_path):
        xml_data = load_xml_from_file(start_date=start_date,
                                      end_date=end_date,
                                      state=state)
        if xml_data != []:
            pit_data = []
            for xml in xml_data:
                pit_data = pit_data + parse_pits_data(xml=xml,
                                                      **params_dict)
        else:
            xml_data = download_xml(start_date=start_date,
                                    end_date=end_date,
                                    state=state)
            pit_data = parse_pits_data(xml=xml_data,
                                       **params_dict)
        save_file = draw_map(save_file=save_file,
                             data=pit_data,
                             **params_dict)
    print(os.path.join(HOST_PARENT_DIR, MAPS_PATH, save_file))
    
    

if __name__ == '__main__':
    log = set_logger('Draw Map', 
                     log_file=os.path.join(LOG_DIR, 'SnowPilotOnMap.log'),
                     level='DEBUG')
    stat_log = set_logger('statistics',
                          log_file=os.path.join(LOG_DIR, 'Statistics.log'),
                          level='INFO')
    
    parser = argparse.ArgumentParser()
    parser.add_argument('-p', '--params', default='{"start_date":"2021-02-01","end_date":"2021-03-30","state":"MT","text_param":"","dot_color_param":"ECT","line_color_param":"","observer_group":"All","show_avs":"Daily AAI mean"}')
    args = parser.parse_args()
    stat_log.info('New quary with {}'.format(args.params))
    main(args)
