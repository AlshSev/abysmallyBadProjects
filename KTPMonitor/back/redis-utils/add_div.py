import sys

def add(div):
	import general, redis
	r = general.get_r()
	return r.sadd('divs', div)

if __name__ == "__main__":
	if (len(sys.argv) != 2):
		print(-1)
	else:
		print(add(sys.argv[1]))