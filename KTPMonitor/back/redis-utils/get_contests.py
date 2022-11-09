import sys

def get(div):
	import general, redis
	r = general.get_r()
	contests = r.smembers(div)
	return sorted([contest.decode() for contest in contests], reverse=True)

if __name__ == "__main__":
	if (len(sys.argv) != 2):
		print(-1)
	else:
		print(get(sys.argv[1]))