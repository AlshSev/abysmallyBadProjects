import sys

def get(nick):
	import general, redis
	r = general.get_r()
	s = r.hgetall(f"st+{nick}")
	ret = [None] * 5
	ret[0] = s["name".encode()].decode()
	ret[1] = s["birth".encode()].decode()
	ret[2] = s["school".encode()].decode()
	ret[3] = s["grade".encode()].decode()
	ret[4] = s["city".encode()].decode()
	return ",,".join(ret)

if __name__ == "__main__":
	if (len(sys.argv) != 2):
		print(-1)
	else:
		print(get(sys.argv[1]))