import sys

def add(div, nick, name, birth, school, grade, city):
	import general, redis
	r = general.get_r()
	r.sadd(f"{div}students", nick)
	try:
		#bruv, update ur redis library for god's love
		key = f"st+{nick}"
		ret = 0
		ret += r.hset(key, "name", name)
		ret += r.hset(key, "birth", birth)
		ret += r.hset(key, "school", school)
		ret += r.hset(key, "grade", grade)
		ret += r.hset(key, "city", city)
		# d = ["name", name, "birth", birth, "school", school, "grade", grade, "city", city]
		# ret = r.hset(f"st+{nick}", items = d)
	except Exception as e:
		print(repr(e))
		return 0
	return ret

if __name__ == "__main__":
	if (len(sys.argv) != 8):
		print(-1)
	else:
		print(add(*sys.argv[1:]))