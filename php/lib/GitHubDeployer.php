<?php

declare(strict_types=1);

class GitHubDeployer
{
    private string $token;
    private string $repo;
    private string $branch;
    private Logger $logger;

    public function __construct(string $token, string $repo, string $branch, Logger $logger)
    {
        $this->token = $token;
        $this->repo = $repo;
        $this->branch = $branch;
        $this->logger = $logger;
    }

    /**
     * MarkdownファイルをGitHubリポジトリにコミットする
     */
    public function deployPost(string $path, string $content, string $commitMessage): bool
    {
        $this->logger->info("Deploying post to GitHub: $path");
        return $this->commitFile($path, $content, $commitMessage);
    }

    /**
     * 画像ファイルをGitHubリポジトリにコミットする
     */
    public function deployImage(string $path, string $binaryData, string $commitMessage): bool
    {
        $this->logger->info("Deploying image to GitHub: $path");
        return $this->commitFile($path, $binaryData, $commitMessage, true);
    }

    /**
     * ファイルをGitHub Contents APIでコミットする
     */
    private function commitFile(string $path, string $content, string $message, bool $isBinary = false): bool
    {
        $url = "https://api.github.com/repos/{$this->repo}/contents/$path";

        // Check if file already exists (need SHA for updates)
        $existingSha = $this->getFileSha($path);

        $data = [
            'message' => $message,
            'content' => $isBinary ? base64_encode($content) : base64_encode($content),
            'branch'  => $this->branch,
        ];

        if ($existingSha !== null) {
            $data['sha'] = $existingSha;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_HTTPHEADER => [
                'Authorization: token ' . $this->token,
                'User-Agent: TED-Blog-Bot/1.0',
                'Content-Type: application/json',
                'Accept: application/vnd.github.v3+json',
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error !== '') {
            $this->logger->error("GitHub API curl error: $error");
            return false;
        }

        if ($httpCode !== 200 && $httpCode !== 201) {
            $this->logger->error("GitHub API returned HTTP $httpCode", [
                'response' => substr((string)$response, 0, 500),
            ]);
            return false;
        }

        $this->logger->info("Successfully committed: $path");
        return true;
    }

    /**
     * 既存ファイルのSHAを取得する（更新時に必要）
     */
    private function getFileSha(string $path): ?string
    {
        $url = "https://api.github.com/repos/{$this->repo}/contents/$path?ref={$this->branch}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: token ' . $this->token,
                'User-Agent: TED-Blog-Bot/1.0',
                'Accept: application/vnd.github.v3+json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return null; // File doesn't exist
        }

        $data = json_decode((string)$response, true);
        return $data['sha'] ?? null;
    }
}
