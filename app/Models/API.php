<?php

/**
 * This class provides a simple API client for making HTTP requests using cURL.
 */

namespace Model;

use ErrorException;
use Exception;

class API
{
    /**
     * Options for the HTTP request.
     *
     * @var array
     */
    private array $options = [
        "HTTP" => [
            "METHOD" => null,
            "HEADER" => null,
            "CONTENT" => null,
        ],
    ];

    /**
     * Constructor to initialize the API client with a URL.
     *
     * @param string $url The URL for the API.
     *
     * @throws Exception If an invalid URL is provided.
     */
    public function __construct(private string $url)
    {
        // Validate the URL during object creation
        if (!filter_var($this->url, FILTER_VALIDATE_URL)) throw new Exception("Invalid URL provided.");
    }

    /**
     * Make a GET request to the API.
     *
     * @param array  $data   Data to include in the request.
     * @param string $header Header for the request.
     *
     * @return string|null The response from the API.
     */
    public function get(array $data, string $header = "Content-type: Application/json"): ?string
    {
        return $this->setOption("GET", $header, $data)->request();
    }

    /**
     * Make a POST request to the API.
     *
     * @param array  $data   Data to include in the request.
     * @param string $header Header for the request.
     *
     * @return string|null The response from the API.
     */
    public function post(array $data, string $header = "Content-type: Application/json"): ?string
    {
        return $this->setOption("POST", $header, $data)->request();
    }

    /**
     * Make a PUT request to the API.
     *
     * @param array  $data   Data to include in the request.
     * @param string $header Header for the request.
     *
     * @return string|null The response from the API.
     */
    public function put(array $data, string $header = "Content-type: Application/json"): ?string
    {
        return $this->setOption("PUT", $header, $data)->request();
    }

    /**
     * Make a DELETE request to the API.
     *
     * @param array  $data   Data to include in the request.
     * @param string $header Header for the request.
     *
     * @return string|null The response from the API.
     */
    public function delete(array $data, string $header = "Content-type: Application/json"): ?string
    {
        return $this->setOption("DELETE", $header, $data)->request();
    }

    /**
     * Set the options for the HTTP request.
     *
     * @param string $method  The HTTP method for the request.
     * @param string $header  Header for the request.
     * @param array  $content Content for the request.
     *
     * @return $this
     */
    private function setOption(string $method, string $header, array $content): self
    {
        $this->options["HTTP"]["METHOD"] = $method;
        $this->options["HTTP"]["HEADER"] = $header;
        $this->options["HTTP"]["CONTENT"] = $method === "GET" ? http_build_query($content) : json_encode($content);

        return $this;
    }

    /**
     * Make the HTTP request and return the response.
     *
     * @return string|null The response from the API.
     *
     * @throws Exception If an error occurs during the request.
     */
    private function request(): ?string
    {
        try {
            // Initialize cURL session
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->options["HTTP"]["METHOD"]);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [$this->options["HTTP"]["HEADER"]]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->options["HTTP"]["CONTENT"]);

            // Execute cURL session
            $response = curl_exec($ch);

            // Check for errors in the cURL request
            if (curl_errno($ch)) throw new Exception("Error in the request: " . curl_error($ch));

            return $response;
        } catch (ErrorException | Exception $th) {
            throw new Exception("Error while making the request: {$th->getMessage()}");
        } finally {
            // Always close the cURL resource
            curl_close($ch);
        }
    }
}
