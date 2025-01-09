
# BookStack API Integration Script

## Features

DISCLAIMER: You should get professional assistance to ensure this script is seciure on your server. I take no responsibility how this scriopt is used and consider this script for educational uise only. Use at YOUR OWN RISK.

This PHP script allows users to interact with the [BookStack API](https://www.bookstackapp.com/api/) to view and download books in various formats. It provides a simple interface to authenticate with the API, fetch a list of books, and download them in PDF, HTML, Plain Text, or Markdown formats.

## Features

- Authenticate with the BookStack API using your API URL, Token ID, and Token Secret.
- Fetch and display a list of books from your BookStack instance.
- Download books in the following formats:
  - PDF
  - HTML
  - Plain Text
  - Markdown
- Batch download multiple books at once.

## Requirements

- PHP 7.4 or higher
- cURL extension enabled
- A valid BookStack instance with API access enabled
- API Token ID and Token Secret from your BookStack instance

## Installation

1. Clone or download this repository to your local server.
2. Place the script (`demo-download-all-formats.php`) in a directory accessible by your web server.
3. Ensure the following directories are writable by the server:
   - `pdf/`
   - `html/`
   - `text/`
   - `markdown/`

## Usage

1. Open the script in your browser.
2. Enter the following details:
   - **API URL**: The base URL of your BookStack API (e.g., `https://your-bookstack-instance.com/api/books`).
   - **Token ID**: Your API Token ID.
   - **Token Secret**: Your API Token Secret.
3. Click **Submit** to authenticate and fetch the list of books.
4. Select the books you want to download.
5. Choose the desired format (PDF, HTML, Plain Text, or Markdown).
6. Click **Download Selected Books** to download the selected books in the chosen format.

## Directory Structure

The script automatically creates the following directories to store downloaded files:

- `pdf/`: Stores books downloaded in PDF format.
- `html/`: Stores books downloaded in HTML format.
- `text/`: Stores books downloaded in Plain Text format.
- `markdown/`: Stores books downloaded in Markdown format.

## Example API URL

The API URL should point to the `books` endpoint of your BookStack instance. For example:

```
https://your-bookstack-instance.com/api/books
```

Refer to the [BookStack API Documentation](https://demo.bookstackapp.com/api/docs#books-list) for more details.

## Notes

- The script uses the BookStack API's `/books` endpoint to fetch the list of books and their metadata.
- The `/books/{id}/export/{format}` endpoint is used to download books in the specified format.
- The script supports batch downloading with a delay to avoid overloading the server.

## Troubleshooting

- Ensure your API Token ID and Token Secret are correct.
- Verify that the API URL is accessible and points to the correct endpoint.
- Check your server's PHP configuration to ensure the `memory_limit` and `max_execution_time` settings are sufficient for large downloads.

## License

This project is licensed under the MIT License.
