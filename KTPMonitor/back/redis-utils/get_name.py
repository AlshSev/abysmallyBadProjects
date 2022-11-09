import sys

def get(nick):
	import general, redis
	r = general.get_r()
	if (len(nick) > 0 and nick[0] == '*'):
		nick = nick[1:]
	ret = r.hget(f"st+{nick}", "name")
	if ret:
		return ret.decode()
	return "???"

if __name__ == "__main__":
	if (len(sys.argv) != 2):
		print(-1)
	else:
		print(get(sys.argv[1]))