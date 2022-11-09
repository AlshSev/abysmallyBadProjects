import sys
import os
sys.path.insert(0, f'{os.path.abspath(os.path.dirname(__file__))}/..')

def add(div, contest):
	import general, redis, contest_info
	r = general.get_r()
	ret = r.srem(div, contest)
	# r.rem(contest)
	contest_info.update_contests(div)
	return ret

if __name__ == "__main__":
	if (len(sys.argv) != 3):
		print(-1)
	else:
		print(add(sys.argv[1], sys.argv[2]))