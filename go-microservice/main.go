package main

import (
	"fmt"
	"log"
	"net/http"
)

func healthHandler(w http.ResponseWriter, r *http.Request) {
	w.WriteHeader(http.StatusOK)
	w.Write([]byte("OK"))
}

func connectToRabbitMQ(rabbitMQURL string) (*amqp091.Connection, error) {
	conn, err := amqp091.Dial(rabbitMQURL)
	if err != nil {
		return nil, fmt.Errorf("failed to connect to RabbitMQ: %w", err)
	}
	return conn, nil
}

func main() {
	rabbitMQURL := "amqp://user:pass@rabbitmq:5672/"

	conn, err := connectToRabbitMQ(rabbitMQURL)
	if err != nil {
		log.Fatalf("Error connecting to RabbitMQ: %v", err)
	}
	defer conn.Close()

	ch, err := conn.Channel()
	if err != nil {
		log.Fatalf("Error opening a channel: %v", err)
	}
	defer ch.Close()

	_, err = ch.QueueDeclare(
		"file_processing", // Название очереди
		true,              // Долговечность очереди
		false,             // Автоматическое удаление
		false,             // Эксклюзивность
		false,             // Ожидание подтверждения
		nil,               // Дополнительные аргументы
	)
	if err != nil {
		log.Fatalf("Error declaring queue: %v", err)
	}

	go func() {
		http.HandleFunc("/health", func(w http.ResponseWriter, r *http.Request) {
			w.WriteHeader(http.StatusOK)
			w.Write([]byte("OK"))
		})
		log.Println("Listening on :8080")
		log.Fatal(http.ListenAndServe(":8080", nil))
	}()

	// Ожидание завершения (чтобы main не завершалась сразу)
	select {}
}
