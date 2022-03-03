from bs4 import BeautifulSoup
from time import sleep
import os
import subprocess

path = os.path.abspath(__file__)[:-len((os.path.basename(__file__)))]
path = path.replace('\\', '/')

import youtube_dl
ytdl_opts = {
    'download_archive': path + 'archive.txt',
    #geo_bypass doesn't seem to work btw
    'geo_bypass': True,
    'format': 'bestaudio/best',
    'postprocessors': [{
        'key': 'FFmpegExtractAudio',
        #stuff below left as an example; don't use it on a whim
        #'preferredcodec': 'mp3',
        #'preferredquality': '192',
    }],
    'outtmpl': path + '%(uploader)s/%(title)s.%(ext)s',
    # 'verbose': True,
}
ytdl = youtube_dl.YoutubeDL(ytdl_opts)

import pprint
pp = pprint.PrettyPrinter(indent=2)

#begin private section
#currently has placeholder values
#subcribed channels must be available to view
collection_channel = 'UCkX7ArB2hOiA8UNlcXO_BrQ'

wanted_playlists = ["PLFVyXoP0G0KfsUEDfEzpnswA2dVdOTFdp", ]
#end private section

from selenium import webdriver
from selenium.webdriver.firefox.options import Options
options = Options()
options.headless = False
driver = webdriver.Firefox(options=options)

#agreeing to use cookies
def warm_driver():
    driver.get('https://www.youtube.com')
    try:
        sleep(3)
        agree = driver.find_elements(by=selenium.By.XPATH,value="/html/body/c-wiz/div/div/div/div[2]/div[1]/div[4]/form/div[1]/div/button")[0]
        agree.click()
        sleep(3)
    except:
        if verbose:
            print("no button")

#because it's faster that way
archive = set()
def prepare_archive():
    from pathlib import Path
    Path(path + 'archive.txt').touch(exist_ok=True)
    f = open(path + 'archive.txt', 'r')
    archive.update([x[8:19] for x in f.readlines()])
    f.close()

verbose = True
def never_stop(func):
    def call_until_oblivion(*args, **kwargs):
        while True:
            try:
                if verbose:
                    print(f'calling {func.__name__}')
                if len(args) or len(kwargs):
                    if verbose:
                        print('with args:')
                        pp.pprint(args)
                        pp.pprint(kwargs)
                ret = func(*args, **kwargs)
                if verbose:
                    print('success')
                return ret
            except Exception as e:
                repr(e)
                if verbose:
                    print('failed')
                sleep(5)
                continue
    return call_until_oblivion

def scroll_down():
    prev = 0
    next_ = driver.execute_script("return document.documentElement.scrollHeight;")
    while prev != next_:
        driver.execute_script("window.scrollTo(0, document.documentElement.scrollHeight);")
        sleep(1)
        prev = next_
        next_ = driver.execute_script("return document.documentElement.scrollHeight;")

#loading collection channel and getting links
@never_stop
def get_channel_links(ch_id):
    url = f'https://www.youtube.com/channel/{ch_id}/channels'
    driver.get(url)
    sleep(5)
    scroll_down()
    html = driver.page_source
    parsed_html = BeautifulSoup(html, 'html.parser')
    return [link['href'] for link in parsed_html.find_all('a', class_='yt-simple-endpoint style-scope ytd-grid-channel-renderer')]

known_errors = ['This video contains content from',
                'This video is not available',
                'This video is unavailable',
                'Unable to extract video data',
                'This content is not available',
                'This video may be inappropriate for some users.', #TODO
                'No video formats found; please report' #???TODO???
                ]
@never_stop
def download_one_video(vid_id):
    if vid_id in archive:
        if verbose:
            print('found in archive')
        return
    try:
        ytdl.download([vid_id])
    except Exception as e:
        for error in known_errors:
            if error in str(e):
                return
        raise e

def download_all_videos(video_ids):
    for video_id in video_ids:
        download_one_video(video_id)

@never_stop
def get_playlist_video_ids(playlist_id):
    url = f'https://www.youtube.com/playlist?list={playlist_id}'
    result = subprocess.run(['youtube-dl', '--get-id', '--flat-playlist', url], stdout=subprocess.PIPE)
    return result.stdout.decode().split()

#doing some voodoo magic to get the real id
@never_stop
def get_id_from_name(name):
    url = f'https://www.youtube.com{name}'
    driver.get(url)
    html = driver.page_source
    parsed_html = BeautifulSoup(html, 'html.parser')
    for meta in parsed_html.find_all(attrs = {"name":"twitter:app:url:ipad"}):
        return "UU" + meta['content'][40:]
    print("failed to get playlist id")

if __name__ == "__main__":
    prepare_archive()
    warm_driver()

    channel_links = get_channel_links(collection_channel)
    print(channel_links)
    total = 0
    for channel_link in channel_links:
        if (channel_link.startswith("/user")):
            playlist_id = get_id_from_name(channel_link)
        else:
            playlist_id = "UU" + channel_link[11:]
        total += 1
        wanted_playlists.append(playlist_id)

    print(f"Total channels: {total}")
    driver.quit()

    for playlist_id in wanted_playlists:
        video_ids = get_playlist_video_ids(playlist_id)
        download_all_videos(video_ids)

