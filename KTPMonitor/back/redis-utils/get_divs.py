import sys

def get():
	import general, redis
	r = general.get_r()
	divs = r.smembers("divs")
	return sorted([div.decode() for div in divs])

if __name__ == "__main__":
	print(get())