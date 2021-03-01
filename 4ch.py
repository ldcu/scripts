import requests
import sys

link = requests.get(url="https://a.4cdn.org/lit/thread/17324185.json")

results = link.json()

books = open("books.txt", "a", encoding="utf-8")

for result in results["posts"]:
    try:
        books.write(result["com"])
        books.write("\n")
    except KeyError:
        continue

books.close()