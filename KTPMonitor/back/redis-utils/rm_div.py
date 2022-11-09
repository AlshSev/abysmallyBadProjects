import sys

def rm(div):
	import general, redis
	r = general.get_r()
	return r.srem('divs', div)

if __name__ == "__main__":
	if (len(sys.argv) != 2):
		print(-1)
	else:
		print(rm(sys.argv[1]))