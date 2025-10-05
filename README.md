# Checkin App
This is a simple Laravel/Alipine app for Story Parlor that streamlines the checkin process for events. With a CSV downloaded from Squarespace, it generates a page that makes checkin's a breeze. You can check off each member of a party (sorted by last name) and add notes (helpful when someone is coming later). Once an entire party is checked in, they will drop down to the bottom so you focus on who remains. To that end, there is also a running counter that displays:

- Tickets available
- People checked in
- People remaining to check in

Additionally, first-time visitors to Story Parlor will be highlighted so you can give them a special welcome.

# Setup
After cloning the repo, you can get it up and running easily with Docker. First build the image from the Dockerfile:

`docker build -t $imageName .`

Then create & run a new container from that image:

`docker run -d -p 8080:80 --name $containerName $imageName`

That will map port 8080 on your machine to port 80 within the container. `init.sh` will create a `.env` file and generate the application key. Then set these environment variables for the database:

## For Postgres
```
DB_CONNECTION
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
```

## For SQLite
```
DB_CONNECTION
DB_DATABASE
```

And you should be off and running!