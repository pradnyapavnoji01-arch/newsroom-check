
<?php
require 'config.php';
require 'includes/analyzer.php';
require 'includes/ai.php';
require_login();

$err = '';
$mode = ($_POST['mode'] ?? 'text') === 'url' ? 'url' : 'text';
$uid = user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    set_time_limit(240);

    $body = trim($_POST['body'] ?? '');
    $url = null;
    $title = null;

    try {
        $url_only = false;

        // Fetch article if URL mode is selected
        if ($mode === 'url') {
            $url = trim($_POST['url'] ?? '');

            try {
                $art = fetch_article($url);
                $body = $art['text'];
                $title = $art['title'];
            } catch (RuntimeException $fx) {
                if (
                    !filter_var($url, FILTER_VALIDATE_URL) ||
                    !url_is_public($url)
                ) {
                    throw $fx;
                }

                $url_only = true;
                $body = $url;
                $title = parse_url($url, PHP_URL_HOST);
            }
        }

        // Validate input
        if (
            !$url_only &&
            count(preg_split('/\s+/u', $body, -1, PREG_SPLIT_NO_EMPTY)) < 4
        ) {
            throw new RuntimeException(
                'Enter at least 4 words.'
            );
        }

        if (mb_strlen($body) > 20000) {
            throw new RuntimeException(
                'Text is too long. Maximum 20,000 characters.'
            );
        }

        // Analyze the text and collect evidence
        $style = analyze_text($body);
        $query = ai_search_query($body, $title, $url, $url_only);
        $ev = collect_evidence($query);

        $res = null;

        // Use AI when available
        if (!ai_enabled()) {
            $_SESSION['flash'] =
                'AI is off. This verdict uses source evidence only.';
        } elseif (ai_used_today($uid) >= AI_DAILY_LIMIT) {
            $_SESSION['flash'] =
                'Daily AI limit reached. Using source evidence only.';
        } else {
            try {
                $res = ai_analyze($body, $url, $url_only, $ev);
            } catch (Throwable $ex) {
                error_log(
                    'Newsroom Check AI error: ' .
                    get_class($ex) . ': ' . $ex->getMessage()
                );

                $_SESSION['flash'] =
                    'AI analysis failed. Using source evidence only.';
            }
        }

        // Fallback when AI is unavailable
        if (!$res) {
            $res = evidence_only_result($ev, $style);
        }

        // Validate the result before saving
        if (
            !is_array($res) ||
            !isset($res['score'], $res['label'])
        ) {
            throw new RuntimeException(
                'The analysis did not return a valid result.'
            );
        }

        // Save analysis to database
        $stmt = db()->prepare(
            'INSERT INTO checks
            (user_id, body, score, verdict, title, source_url, ai_json)
            VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $uid,
            $body,
            $res['score'],
            $res['label'],
            $title,
            $url,
            json_encode(
                $res,
                JSON_UNESCAPED_UNICODE |
                JSON_INVALID_UTF8_SUBSTITUTE
            )
        ]);

        // Redirect to result page
        header('Location: result.php?id=' . db()->lastInsertId());
        exit;

    } catch (Throwable $ex) {
        error_log(
            'Newsroom Check analysis error: ' .
            get_class($ex) . ': ' . $ex->getMessage()
        );

        $err = $ex instanceof RuntimeException
            ? $ex->getMessage()
            : 'Analysis failed. Please check the server error log.';
    }
}

$title = 'Check news';
require 'includes/header.php';
?>

<h1>Check a news story</h1>

<p class="lead">
    Paste a headline, viral message, article or link in English,
    Hindi or Marathi. The system searches sources and provides a verdict.
</p>

<div class="card">

<?php if ($err): ?>
    <p class="alert"><?= e($err) ?></p>
<?php endif; ?>

<?php if (!ai_enabled()): ?>
    <p class="note">
        <b>AI is off.</b> Add your Groq API key to
        <code>config.php</code> to enable AI analysis.
        Source-based results are still available.
    </p>
<?php endif; ?>

<form method="post" id="f">
    <input type="hidden" name="csrf" value="<?= csrf() ?>">

    <div class="tabs">
        <label>
            <input type="radio" name="mode" value="text"
                <?= $mode === 'text' ? 'checked' : '' ?>>
            Paste text / headline
        </label>

        <label>
            <input type="radio" name="mode" value="url"
                <?= $mode === 'url' ? 'checked' : '' ?>>
            Article link
        </label>
    </div>

    <div id="pt">
        <label for="body">News, headline or claim</label>
        <textarea
            id="body"
            name="body"
            rows="8"
            maxlength="20000"
            placeholder="Enter a news headline or claim"
        ><?= e($_POST['body'] ?? $_GET['q'] ?? '') ?></textarea>
    </div>

    <div id="pu">
        <label for="url">Article URL</label>
        <input
            id="url"
            name="url"
            type="url"
            placeholder="https://example.com/news"
            value="<?= e($_POST['url'] ?? '') ?>"
        >
    </div>

    <div class="row">
        <button class="pri" id="go" type="submit">
            Check credibility
        </button>

        <span class="sp"></span>

        <span id="samples" class="meta">
            Try:
            <button type="button" id="s1">Clickbait</button>
            <button type="button" id="s2">Real-style report</button>
            <button type="button" id="s3">Viral claim</button>
        </span>
    </div>

    <p class="meta" id="wait" hidden>
        Checking news sources. Please keep this tab open.
    </p>
</form>
</div>

<script>
const samples = [
    "SHOCKING! Doctors hate this secret miracle cure. Experts say it is guaranteed to work. Share before it is deleted!",
    "The health ministry said on Monday that vaccination coverage rose to 82 percent, according to data from an annual survey.",
    "Breaking: RBI will ban all Rs 500 notes next week, according to a viral message. Check the claim before sharing."
];

const $ = id => document.getElementById(id);
const f = $('f');

function sync() {
    const isUrl = f.querySelector('[name="mode"]:checked').value === 'url';

    $('pt').hidden = isUrl;
    $('pu').hidden = !isUrl;
    $('samples').hidden = isUrl;
    $('body').required = !isUrl;
    $('url').required = isUrl;
}

f.querySelectorAll('[name="mode"]').forEach(r => {
    r.addEventListener('change', sync);
});

sync();

[1, 2, 3].forEach(i => {
    $('s' + i).addEventListener('click', () => {
        $('body').value = samples[i - 1];
    });
});

f.addEventListener('submit', () => {
    $('go').disabled = true;
    $('go').textContent = 'Checking...';
    $('wait').hidden = false;
});
</script>

<?php require 'includes/footer.php'; ?>
