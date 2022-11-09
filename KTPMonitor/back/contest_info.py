import sys
import os
file_path = os.path.abspath(os.path.dirname(__file__))
sys.path.insert(0, file_path)
import cfapi
sys.path.insert(0, f'{file_path}/redis-utils')
import general
import get_contests
import get_divs
from time import sleep


def get_header(data):
    header = ["Ник", "Сумма"]
    for problem in data['problems']:
        header.append(problem['index'])

    header[-1] += '\n'
    return ",,".join(header)

def get_standings(data):
    standings = []
    for team in data['rows']:
        participant = [team['party']['members'][0]['handle']]
        if team['party']['participantType'] == 'PRACTICE':
            participant[0] = '*' + participant[0]
        participant.append(str(int(team['points'])))
        for problem in team['problemResults']:
            result = ""
            if int(problem['points']) == 1:
                result += '+'
            else:
                result += '-'
            participant.append(result)
        standings.append(",,".join(participant))
    return "\n".join(standings)

def update_contest(div):
    contest_info = []
    contest_info.append("Название,,ID,,Создатель,,Длительность")

    # with open(f"../data/{div}/contests/contest_ids.txt", "r", encoding="utf-8") as f:
    #     lines = f.readlines()
    r = general.get_r()
    lines = get_contests.get(div)
    for line in lines:
        # print(line)
        for i in range(3):
            req = cfapi.authorized_request("contest.standings", [("contestId", line), ("showUnofficial", "true")])
            if req != None and req['status'] == "OK":
                break
            sleep(1)
        try:
            data = req['result']
            r.set(line, get_header(data) + get_standings(data))
            # with open(f"{file_path}/../data/{div}/contests/{line}", "w", encoding="utf-8") as f:
            #     f.write(get_header(data))
            #     f.write(get_standings(data))
            data = data['contest']
            contest_info.append(f"{data['name']},,{data['id']},,{data['preparedBy']},,{int(data['durationSeconds']) // 3600}")
        except Exception as e:
            continue
    r.set(f"{div}description", "\n".join(contest_info))
    # with open(f"../data/{div}/contests/descriptions.txt", "w", encoding="utf-8") as f:
    #     f.write("\n".join(contest_info))

def update_contests():
    divs = get_divs.get()
    for div in divs:
        update_contest(div)
