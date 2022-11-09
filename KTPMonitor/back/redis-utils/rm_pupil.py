import sys

def rm(div, nick):
	import general, redis
	r = general.get_r()
	ret = 0
	ret += r.srem(f"{div}students", nick)
	ret += r.delete(f"st+{nick}")
	return ret

if __name__ == "__main__":
	if (len(sys.argv) != 3):
		print(-1)
	else:
		print(rm(sys.argv[1], sys.argv[2]))