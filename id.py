import random
import string

id = "".join(random.SystemRandom().choice(string.digits) for _ in range(4))

print(id)
