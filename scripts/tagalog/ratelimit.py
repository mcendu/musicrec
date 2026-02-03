import time


class RateLimiter:
    def __init__(self, requests: int, period: float):
        self.max_requests = requests
        self.period = period
        self.requests = 0
        self.next_reset = 0.0

    def wait(self):
        """
        Check if a request can be sent to the desired API now, and sleep
        if it cannot.
        """
        if time.time() >= self.next_reset:
            self.requests = 0
            self.next_reset = 0

        if self.requests == self.max_requests:
            time.sleep(self.next_reset - time.time())
            self.requests = 0
            self.next_reset = 0

        if self.requests == 0:
            self.next_reset = time.time() + self.period

        self.requests += 1
