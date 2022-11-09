import gsapi

div_to_row = {'B': 3, 'C': 2, 'D': 1}

def get_people(div):
    ret = set()
    names = gsapi.get_data("Списки и расписание", 1, [div_to_row[div]])
    for name in names[0][1:]:
        ret.add(name.strip())
    return ret

def update_people(div):
    gsapi.authorize()
    names = get_people(div)
    #имя,,ник,,возраст,,школа,,класс,,город
    columns = [2, 8, 4, 6, 7, 5]
    data = gsapi.get_data("Анкета для школьников (Ответы)", 0, columns)
    info = []
    info.append("Имя,,Ник,,Возраст,,Школа,,Класс,,Город")
    for i in range(len(data[0])):
        if data[0][i].strip() not in names:
            continue
        info.append([])
        for j in range(len(columns)):
            info[-1].append(data[j][i].strip())
        info[-1] = ",,".join(info[-1])
    f = open(f"../data/{div}/people/students.txt", "w", encoding="utf8")
    f.write("\n".join(info))
    f.close()
    return names

def get_correlation(div):
    with open(f'../data/{div}/people/students.txt', "r", encoding="utf8") as f:
        lines = f.readlines()
    names = {}
    for line in lines[1:]:
        name, nick = line.split(",,")[:2]
        names[nick] = name
    return names

def update_stats(div):
    names = get_correlation(div)
    stats = {}
    cnames = []
    with open(f"../data/{div}/contests/descriptions.txt", "r", encoding="utf-8") as f:
        descriptions = f.readlines()
    for nick, name in names.items():
        stats[nick] = [0] * (len(descriptions) - 1)
    loc_cont_id = 0
    for contest_desc in descriptions[1:]:
        cname, cid = contest_desc.split(",,")[:2]
        cnames.append(cname)
        with open(f"../data/{div}/contests/{cid}", "r", encoding="utf-8") as f:
            standings = f.readlines()
        new_contest = ["Имя,," + standings[0]]
        for line in standings[1:]:
            data = line.split(",,")
            nick = data[0]
            if nick.startswith("*"):
                nick = nick[1:]
            if nick not in stats:
                new_contest.append("???,," + line)
                continue
            new_contest.append(names[nick] + ",," + line)
            stats[nick][loc_cont_id] += int(data[1])
        with open(f"../data/{div}/contests/{cid}", "w", encoding="utf-8") as f:
            f.write("".join(new_contest))
        loc_cont_id += 1
    info = []
    info.append(f"Имя,,Ник,,{',,'.join(cnames)}")
    for nick, results in stats.items():
        line_info = [names[nick], nick]
        for i in results:
            line_info.append(str(i))
        info.append(",,".join(line_info))
    with open(f"../data/{div}/people/stats.txt", "w", encoding="utf-8") as f:
        f.write("\n".join(info))
