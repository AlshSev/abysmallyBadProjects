import sys

def check(fingerprint):
	import general, redis
	r = general.get_r()
	return r.exists(fingerprint)

if __name__ == "__main__":
	if (len(sys.argv) != 2):
		print(-1)
	else:
		print(check(sys.argv[1]))