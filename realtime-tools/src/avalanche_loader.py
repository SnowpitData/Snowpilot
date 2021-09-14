# -*- coding: utf-8 -*-
"""
Created on Wed Jul 28 09:36:58 2021

@author: Ron Simenhois
"""
import os
import numpy as np
import pandas as pd
import geopandas as gpd

from config import AVALANCHES_PATH, GEODATA_PATH

def load_zone_polygons(state='CO'):
    '''
    This function loads zones name and their polygons in a geopandas 
    dataframe
    
    Parameters
    ----------
    state : str
        DESCRIPTION.

    Returns
    -------
    zones : geopandas Dataframe
        a geopandas dataframe with zone names and their polygons.

    '''
    geodata_path_dict = {'CO': os.path.join(GEODATA_PATH, 'CAIC_Zones.geojson'),
                         'MT': os.path.join(GEODATA_PATH, 'GNFAC_Zones.geojson')}
    zone_geo_data = geodata_path_dict[state]
    zones = gpd.read_file(zone_geo_data, crs="epsg:4326")
    zones = zones[['zone_name', 'geometry']]
    zones = zones.to_crs("epsg:3857")#.to_crs({'init' :'epsg:4326'}) 
    
    return zones


def load_avalanches_by_date(start_date, 
                            end_date,
                            state):
    '''
    This function load avalanches record between dated

    Parameters
    ----------
    start_date : str
        DESCRIPTION.
    end_date : str
        DESCRIPTION.

    Returns
    -------
    avalanches : Pandas DataFrame
        DataFrame of all the avalanches between star and end dates
        with columns: Date, Zone and D size (as a number).

    '''

    avalanche_path = {'CO': os.path.join(AVALANCHES_PATH,'CAIC_avalanches.csv'),
                      'MT': os.path.join(AVALANCHES_PATH, 'GNFAC_avalanches.csv')}
    avalanches = pd.read_csv(avalanche_path[state],
                             usecols=['Date', 'BC Zone', 'Dsize'])
    avalanches = avalanches[avalanches['Date'].between(start_date, end_date)]
    avalanches['Dsize'] = np.where(avalanches['Dsize'].str.startswith('D'), avalanches['Dsize'], None)
    avalanches = avalanches.dropna(subset=['Dsize'])
    avalanches['Dsize val'] = avalanches['Dsize'].str[1:].astype(float)
    
    return avalanches


def get_avalanche_activity_by_zone(start_date,
                                   end_date,
                                   method,
                                   state='CO'):
    '''
    

    Parameters
    ----------
    start_date : str
        date strin in foremar: YYYY/MM/DD.
    end_date : str
        date strin in foremar: YYYY/MM/DD.
    method : str
        AAI - to get the average daily AAI for the give range.
        counts - for the average daily avalanche count.
    state : str, optional
        State to pull the data for. The default is 'CO'.

    Returns
    -------
    avalanches_by_zone : geopandas DataFrame
        A geopandas DataFrame with "zone_mane" and "totals" columns.

    '''
    zones = load_zone_polygons(state=state)
    avalanches = load_avalanches_by_date(start_date=start_date, 
                                         end_date=end_date,
                                         state=state)
    day_count = (pd.Timestamp(end_date)-pd.Timestamp(start_date)).days
    
    if method == 'AAI':
        avalanches['totals'] = np.power(10, avalanches['Dsize val']-3)
    else:
        avalanches['totals'] = 1
    avalanches_by_zone = avalanches.groupby('BC Zone')[['totals']].sum()
    
    for z, desc in avalanches.groupby('BC Zone')['Dsize']:
        desc = desc.value_counts().sort_index()
        description = '<br>'.join(['{:4}: {:3} {}'.format(i, desc.loc[i],
                                                          'avalanches' if desc.loc[i] > 1 else 'avalanche') for i in desc.index])
        avalanches_by_zone.loc[z, 'description'] = description
    
    avalanches_by_zone['totals'] = (avalanches_by_zone['totals'] / day_count).round(2)
    avalanches_by_zone['zone_name'] = avalanches_by_zone.index
    avalanches_by_zone = zones.merge(avalanches_by_zone, on='zone_name')
    
    return avalanches_by_zone

def update_avalanche_data(state):
    '''
    This function should run every nigth and update the avalanche ocurances 
    for every state.

    Parameters
    ----------
    state : str
        DESCRIPTION.

    Returns
    -------
    None.

    '''
    
    if state=='MT':
        all_avs = []
        for _id in [440]:
            year_id = f'?field_advisory_year_target_id={_id}'
            try:
                avalanches = pd.read_html('https://www.mtavalanche.com/avalanche-activity-export'+year_id)[0]
            except ValueError:
                continue
            avalanches = avalanches[['Date', 'Region', 'D size']].dropna(subset=['D size'])
            avalanches['D size'] = 'D' + avalanches['D size'].astype(str)
            avalanches.columns = ['Date', 'BC Zone', 'Dsize']
            zones = load_zone_polygons(state='MT') 
            avalanches = avalanches[avalanches['BC Zone'].isin(zones['zone_name'])]
            all_avs.append(avalanches)
        old_avalanches = pd.read_csv(os.path.join(AVALANCHES_PATH, 'GNFAC_avalanches.csv'))
        new_avalanches = pd.concat(all_avs + [old_avalanches]).drop_duplicates(keep='first')
        
        new_avalanches.to_csv(os.path.join(AVALANCHES_PATH, 'GNFAC_avalanches.csv'))
      
        
def main():
    for state in ['MT']:
        update_avalanche_data(state)
        
if __name__ == '__main__':
    main()