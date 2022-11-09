import sys

def get(cid):
	import general, redis
	r = general.get_r()
	return r.get(cid).decode()

if __name__ == "__main__":
	if (len(sys.argv) != 2):
		print(-1)
	else:
		print(get(sys.argv[1]))