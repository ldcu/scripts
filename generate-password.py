import string
import random

pw = "".join(
    random.SystemRandom().choice(
        string.ascii_uppercase
        + string.ascii_lowercase
        + string.digits
        + string.punctuation
    )
    for _ in range(20)
)

print(pw)
