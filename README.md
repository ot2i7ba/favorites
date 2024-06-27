# favorites
Simple Web-Link-Tracker is a simple script that saves URLs submitted via a bookmarklet. The saved links are stored in a JSON file (favorites.json). A session ID is used to avoid IP tracking. There are also some security measures to prevent unwanted access and blacklisting.

Considering the fact that no database is used, the performance is only acceptable for a small number (tested with 1000) of URLs. However, performance may decrease for a large number of links, since the entire JSON array must be loaded and stored for each read or write. However, this is perfectly adequate for personal use.

### Some security measures
- The script uses a session ID to avoid IP tracking.
- A secret value ($secret_value) is checked to ensure that only authorized users can run the script.
- Logging unauthorized access (intruders.json) with timestamp, session ID, IP address, url and title.
- Prevents concurrent access to favorites.json to avoid data inconsistency and data loss.
- Automatically removes old entries (MAX_DAYS_TO_KEEP) that are older than X days.
- Use a blacklist file (blacklist.txt) to block URLs from specific domains.
- Checks submitted URLs and title for validity and cleans them up.
- The number of requests per minute is limited to prevent abuse.

### How to use?
1. Upload the files (favorites.php, favlogic.php, blacklist.txt) to your web server using FTP.
2. Write domains in the "**blacklist.txt**" which should not be saved. One domain per line.
3. Search for "**YOUR_SECRET_VALUE_HERE**" in "**favlogic.php**" and replace it with your personal magic word.
4. Create a bookmark and paste there the content of bookmarklet.txt instead of the URL.
5. Replace the value "**YOUR_SECRET_VALUE_HERE**" with your personal magic word.
6. Replace "**YOUR_DOMAIN_HERE.COM**" with your domain and change the path to favorites.php.

### How it works?
Example. If you want to watch your favorite YouTube video later, call it up and click on the bookmarklet [4, 5, 6] you just created. The bookmarklet sends the title of your video and the URL to the video to your Simple Bookmarklet Web-Link-Tracker [1, 2, 3] and is stored there after verification in the "**favorites.json**". Later you can view the saved URLs by calling the link to your Simple Bookmarklet Web-Link-Tracker including your personal magic word. This may look like this:

- https://YOUR_DOMAIN_HERE.COM/favorites.php?secret=YOUR_SECRET_VALUE_HERE

### What else?
You could place the sample "**.htaccess**" file where you saved the other files. This prevents direct access to the favorites.json, favorites.lock, intruder.json and blacklist.txt files, for example. Additionally, the set headers are supposed to prevent clickjacking. Well, at least they should. It may be that the .htaccess needs other adjustments depending on how your server has been configured. Don't play around with it if you don't know anything about it. I hope I have not forgotten anything. Github is so exciting and still new to me. =)

## License
This project is licensed under the **[MIT license](https://github.com/ot2i7ba/favorites/blob/main/LICENSE)**, providing users with flexibility and freedom to use and modify the software according to their needs.

## Disclaimer
This project is provided without warranties. Users are advised to review the accompanying license for more information on the terms of use and limitations of liability.
