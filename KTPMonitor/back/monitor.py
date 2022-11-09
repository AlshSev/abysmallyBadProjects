import contest_info
import perfomance_info
import time

minutes_per_update = 15
delay = 60.0 * minutes_per_update

def main():
    start = time.time()
    while True:
        contest_info.update_contests()
        perfomance_info.update_perfomance()

        time.sleep(delay - (time.time() - start) % delay)

if __name__ == "__main__":
    main()
