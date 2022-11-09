import sys

def get(div):
	import general, redis
	r = general.get_r()
	return sorted([nick.decode() for nick in r.smembers(f"{div}students")])

if __name__ == "__main__":
	if (len(sys.argv) != 2):
		print(-1)
	else:
		print(get(sys.argv[1]))