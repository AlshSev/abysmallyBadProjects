import redis

def get_r():
	return redis.Redis(host="localhost", port=6969)
