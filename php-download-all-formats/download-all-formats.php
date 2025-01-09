<?php
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);
// https://github.com/codinginbarn/bookstack-api-scripts
?><!DOCTYPE html>
<html>
<head>
    <title>BookStack Get Books API Example</title>
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="url"] {
            width: 100%;
            padding: 8px;
        }
        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .book-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .book-cover {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .book-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .book-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .book-meta {
            font-size: 12px;
            color: #888;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            color: #a94442;
        }
        .book-select {
            margin-bottom: 10px;
        }
        .download-form {
            margin-top: 20px;
        }
        .format-select {
            margin: 10px 0;
        }
        .format-select label {
            display: inline-block;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="POST">
            <div class="form-group">
                <label>API URL:</label>
                <input type="url" name="api_url" required value="https://your-bookstack-instance.com/api/books">
            </div>
            <div class="form-group">
                <label>Token ID:</label>
                <input type="text" name="token_id" required>
            </div>
            <div class="form-group">
                <label>Token Secret:</label>
                <input type="text" name="token_secret" required>
            </div>
            <button type="submit">Submit</button>
        </form>

        <?php
			function createFormatDirectory($format) {
				$formatDirs = [
					'pdf' => __DIR__ . '/pdf',
					'html' => __DIR__ . '/html',
					'txt' => __DIR__ . '/text',
					'markdown' => __DIR__ . '/markdown'
				];
				
				$dir = $formatDirs[$format];
				if (!file_exists($dir)) {
					mkdir($dir, 0755, true);
				}
				return $dir;
			}

			function downloadBook($id, $name, $token_id, $token_secret, $api_url, $format) {
				$downloadDir = createFormatDirectory($format);
				$authorization = "Token {$token_id}:{$token_secret}";
				$filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
				
				switch ($format) {
					case 'pdf':
						$filename .= '.pdf';
						$exportFormat = 'pdf';
						break;
					case 'html':
						$filename .= '.html';
						$exportFormat = 'html';
						break;
					case 'txt':
						$filename .= '.txt';
						$exportFormat = 'plaintext';
						break;
					case 'markdown':
						$filename .= '.md';
						$exportFormat = 'markdown';
						break;
				}
				
				$filepath = $downloadDir . '/' . $filename;
				
				$baseUrl = preg_replace('/\/api\/books.*$/', '', $api_url);
				$downloadUrl = $baseUrl . "/api/books/{$id}/export/" . $exportFormat;
				
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_URL => $downloadUrl,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_HTTPGET => true,
					CURLOPT_HTTPHEADER => array(
						"Authorization: {$authorization}",
						"Accept-Encoding: gzip, deflate"
					),
					CURLOPT_ENCODING => "",
					CURLOPT_TIMEOUT => 60,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_BUFFERSIZE => 128000
				));
				
				$response = curl_exec($curl);
				$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				curl_close($curl);
				
				if ($httpCode === 200) {
					file_put_contents($filepath, $response);
					return true;
				}
				return false;
			}

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'download_books' && !empty($_POST['selected_books'])) {
                $success = [];
                $errors = [];
                $format = $_POST['format'];
                
                $batches = array_chunk($_POST['selected_books'], 5);
                
                foreach ($batches as $batch) {
                    foreach ($batch as $bookId) {
                        if (isset($_POST['book_names'][$bookId])) {
                            $bookName = $_POST['book_names'][$bookId];
                            if (downloadBook($bookId, $bookName, $_POST['token_id'], $_POST['token_secret'], $_POST['api_url'], $format)) {
                                $success[] = "Successfully downloaded: " . htmlspecialchars($bookName) . " in " . strtoupper($format) . " format";
                            } else {
                                $errors[] = "Failed to download: " . htmlspecialchars($bookName);
                            }
                        }
                    }
                    usleep(500000);
                }
                
                clearstatcache();
                
                if (!empty($success)) {
                    echo '<div class="message success">';
                    foreach ($success as $msg) echo $msg . '<br>';
                    echo '</div>';
                }
                
                if (!empty($errors)) {
                    echo '<div class="message error">';
                    foreach ($errors as $msg) echo $msg . '<br>';
                    echo '</div>';
                }
            }

            $api_url = $_POST['api_url'];
            $token_id = $_POST['token_id'];
            $token_secret = $_POST['token_secret'];
            $authorization = "Token {$token_id}:{$token_secret}";
            
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $api_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPGET => true,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: {$authorization}",
                    "Accept-Encoding: gzip, deflate"
                ),
                CURLOPT_ENCODING => "",
                CURLOPT_TIMEOUT => 30
            ));
            
            $response = curl_exec($curl);
            
            if (curl_errno($curl)) {
                echo '<div style="margin-top: 20px; color: red;">Error: ' . curl_error($curl) . '</div>';
            } else {
                $data = json_decode($response, true);
                if ($data) {
                    echo '<form method="POST" class="download-form">';
                    echo '<input type="hidden" name="action" value="download_books">';
                    echo '<input type="hidden" name="api_url" value="' . htmlspecialchars($api_url) . '">';
                    echo '<input type="hidden" name="token_id" value="' . htmlspecialchars($token_id) . '">';
                    echo '<input type="hidden" name="token_secret" value="' . htmlspecialchars($token_secret) . '">';
                    
                    echo '<div class="format-select">';
                    echo '<label><input type="radio" name="format" value="pdf" checked> PDF</label>';
                    echo '<label><input type="radio" name="format" value="html"> HTML</label>';
                    echo '<label><input type="radio" name="format" value="txt"> Plain Text</label>';
                    echo '<label><input type="radio" name="format" value="markdown"> Markdown</label>';
                    echo '</div>';
                    
                    echo '<div class="book-grid">';
                    
                    $items = isset($data['data']) ? $data['data'] : array($data);
                    
                    foreach ($items as $item) {
                        echo '<div class="book-card">';
                        
                        echo '<div class="book-select">';
                        echo '<input type="checkbox" name="selected_books[]" value="' . $item['id'] . '" id="book_' . $item['id'] . '">';
                        echo '<input type="hidden" name="book_names[' . $item['id'] . ']" value="' . htmlspecialchars($item['name']) . '">';
                        echo '</div>';
                        
                        if (isset($item['cover']) && is_array($item['cover']) && isset($item['cover']['url'])) {
                            echo '<img src="' . htmlspecialchars($item['cover']['url']) . '" class="book-cover" alt="Book cover">';
                        } else {
                            echo '<div class="book-cover" style="background: #eee; display: flex; align-items: center; justify-content: center;">No Cover</div>';
                        }
                        
                        if (isset($item['name'])) {
                            echo '<div class="book-title">' . htmlspecialchars($item['name']) . '</div>';
                        }
                        
                        if (isset($item['description'])) {
                            echo '<div class="book-description">' . htmlspecialchars($item['description']) . '</div>';
                        }
                        
                        echo '<div class="book-meta">';
                        if (isset($item['created_at'])) {
                            echo 'Created: ' . date('M d, Y', strtotime($item['created_at'])) . '<br>';
                        }
                        if (isset($item['updated_at'])) {
                            echo 'Updated: ' . date('M d, Y', strtotime($item['updated_at'])) . '<br>';
                        }
                        if (isset($item['id'])) {
                            echo 'ID: ' . htmlspecialchars($item['id']);
                        }
                        echo '</div>';
                        
                        echo '</div>';
                    }
                    echo '</div>';
                    
                    echo '<button type="submit">Download Selected Books</button>';
                    echo '</form>';
                    
                    if (isset($data['total'])) {
                        echo '<div style="margin-top: 20px;">Total Items: ' . $data['total'] . '</div>';
                    }
                } else {
                    echo '<div style="margin-top: 20px; color: red;">Invalid JSON response received</div>';
                    echo '<pre>' . htmlspecialchars($response) . '</pre>';
                }
            }
            curl_close($curl);
        }
        ?>
    </div>
</body>
</html>
