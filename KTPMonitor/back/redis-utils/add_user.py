import sys

def add(login, password):
	import general, redis
	r = general.get_r()
	if r.exists(f"u+{login}+{password}") != "0":
		r.set(f"u+{login}+{password}", "")
		return 1
	return 0

if __name__ == "__main__":
	if (len(sys.argv) != 3):
		print(-1)
	else:
		print(add(sys.argv[1], sys.argv[2]))