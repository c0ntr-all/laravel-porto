FROM ubuntu:22.04

RUN apt-get update && \
    apt-get install -y unzip unrar p7zip-full gzip tar curl ca-certificates && \
    rm -rf /var/lib/apt/lists/*

COPY ./main /app/main

WORKDIR /app

RUN chmod +x ./main

CMD ["./main"]