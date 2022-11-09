import sys
import os
file_path = os.path.abspath(os.path.dirname(__file__))
sys.path.insert(0, file_path)
import cfapi
sys.path.insert(0, f'{file_path}/redis-utils')
import general
import get_descriptions
import get_divs
import get_pupils
import get_contest
import get_name

def update_div_perfomance(div):
    pupils = get_pupils.get(div)
    contests = get_descriptions.get(div).split('\n')
    stats = {}
    cnames = []
    for nick in pupils:
        stats[nick] = [set() for i in range(len(contests) - 1)]
    for loc_cont_id, contest in enumerate(contests[1:]):
        cname, cid = contest.split(",,")[:2]
        cnames.append(cname)
        standings = get_contest.get(cid).split('\n')
        for line in standings[1:]:
            data = line.split(",,")
            nick = data[0]
            if nick[0].startswith("*"):
                nick = nick[1:]
            if nick not in stats:
                continue
            for task, attempt in enumerate(data[2:]):
                if attempt == '+':
                    stats[nick][loc_cont_id].add(task)
    info = []
    info.append(f"Имя,,Ник,,{',,'.join(cnames)}")
    for nick, results in stats.items():
        line_info = [get_name.get(nick), nick]
        for st in results:
            line_info.append(str(len(st)))
        info.append(",,".join(line_info))
    r = general.get_r()
    r.set(f"{div}perf", "\n".join(info))
    print("\n".join(info))

def update_perfomance():
    divs = get_divs.get()
    for div in divs:
        update_div_perfomance(div)