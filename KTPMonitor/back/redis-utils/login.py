import sys

def login(login, password):
	import general, redis
	r = general.get_r()
	if (r.exists(f"u+{login}+{password}")):
		fingerprint = f"f{login}"
		r.set(fingerprint, "", 1200)
		return fingerprint
	else:
		return 0

if __name__ == '__main__':
	if (len(sys.argv) != 3):
		print(-1)
	else:
		print(login(sys.argv[1], sys.argv[2]))