<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Breakfast and Coffee</title>

    <meta name="description" content="A curated list of links about coffee.">
    <link rel="alternate" href="https://granary.io/url?input=html&output=atom&url=https://breakfastand.coffee" type="application/atom+xml" title="Breakfast and Coffee Web Feed">
    <link rel="me" href="mailto:jamesg@jamesg.blog" />
    <link href="https://webmention.io/breakfastand.coffee/webmention" rel="webmention">
    <link rel="icon" href="https://breakfastand.coffee/favicon.ico" />
    <link rel="preload" href="https://breakfastand.coffee/mascot2.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Mozilla+Text:wght@200..700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: light-dark(#724e17, #ffcc80);
            --secondary-color: #bc8f4b;
            --background-color: light-dark(#f7f7f7, rgb(24, 24, 24));
        }
        * {
            box-sizing: border-box;
            color: var(--primary-color);
            line-height: 1.5;
            color-scheme: light dark;
        }
        html {
            background-color: var(--background-color);
            font-family: 'Mozilla Text', serif;
            padding: 3rem;
            border-top: 0.25rem solid var(--secondary-color);
            border-left: 0.25rem solid var(--secondary-color);
            min-height: 100vh;
        }
        h1, h3 {
            font-family: 'Fraunces', serif;
            color: var(--primary-color);
        }
        h3 {
            font-size: 1.4rem;
            border-bottom: 1px solid var(--primary-color);
        }
        input[type="checkbox"] {
            accent-color: var(--primary-color);
            color: transparent;
        }
        #list {
            ol {
                margin: 0;
                padding: 0;
                list-style-type: none;
            }
            li a, li p {
                margin: 0;
            }
            li {
                margin-bottom: 1rem;
            }
        }
        h1, h1 + * {
            margin: 0;
        }
        h1 a {
            text-decoration: none;
        }
        header {
            display: flex;
            flex-direction: row;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1rem;
        }
        header p {
            display: flex;
            gap: 0.5rem;
        }
        header *, a {
            color: var(--primary-color);
        }
        li p {
            color: var(--secondary-color);
        }
        #mascot {
            cursor: pointer;
        }
        #submit {
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            padding: 1rem;
            width: 100%;
            display: none;
        }
        #submit:target {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        #submit h2 {
            margin: 0;
        }
        input {
            width: 100%;
            background: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            font-size: 1.25rem;
        }
        input[type="url"], input[type="text"] {
            margin-bottom: 1rem;
        }
        label {
            display: grid;
            grid-template-columns: 10ch auto;
            gap: 1rem;
            width: 100%;
            font-weight: 500;
        }
        label:not(:has(input)) {
            display: block;
        }
        #main a:visited {
            color: var(--secondary-color);
        }
        label + label {
            margin-top: 0.5rem;
        }
        button {
            background: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            padding: 0.5rem;
            text-decoration: none;
            width: 100%;
            font-size: 1.2rem;
            margin-top: 1rem;
            cursor: pointer;
        }
        label input {
            width: max-content;
        }
        #announcement {
            width: 100%;
            border: 1px solid var(--primary-color);
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
        }
        #submit-button {
            margin-left: auto;
            a {
                color: var(--primary-color);
                border: 1px solid var(--primary-color);
                padding: 1rem;
                text-decoration: none;
            }
        }
        body:has(#submit:target) #list {
            display: none;
        }
        @media screen and (max-width: 600px) {
            html {
                padding: 1rem;
            }
            header * {
                font-size: 1rem;
            }
            header h1 a {
                font-size: 1.5rem;
            }
            #submit:target {
                display: block;
            }
        }
    </style>
</head>
<body>
     <?php
        require '../vendor/autoload.php';

        // disable php warnings
        error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

        // if post and json header
        // print method
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'], 'application/json') === 0) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }

        $admin_pass = getenv("BREAKFAST_AND_COFFEE_ADMIN");

        $db = new SQLite3('../posts.db', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
        
        if (!$db) {
            die("There was a problem loading the page. Please contact jamesg@jamesg.blog.");
        }

        // source is domain of final url
        // via domain is the url that submitted, if applicable
        $db->query("
        CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            url TEXT UNIQUE NOT NULL,
            source TEXT NOT NULL,
            via_domain TEXT,
            via_url TEXT,
            tags TEXT NOT NULL,
            date TEXT NOT NULL
        )");

        function insert_record ($title, $url, $source, $via_domain, $via_url, $tags, $date) {
            global $db;
            $initial_insert = $db->prepare("INSERT OR IGNORE INTO posts (title, url, source, via_domain, via_url, tags, date) VALUES (:title, :url, :source, :via_domain, :via_url, :tags, :date)");
            $initial_insert->bindValue(':title', $title);
            $initial_insert->bindValue(':url', $url);
            $initial_insert->bindValue(':source', $source);
            $initial_insert->bindValue(':via_domain', $via_domain);
            $initial_insert->bindValue(':via_url', $via_url);
            $initial_insert->bindValue(':tags', $tags);
            $initial_insert->bindValue(':date', $date);
            $initial_insert->execute();
        }

        if ($_GET && $_GET["pass"]) {
            if ($_GET["pass"] === $admin_pass) {
                $delete = $db->prepare("DELETE FROM posts WHERE url = :url");
                $delete->bindValue(':url', $_GET["delete"]);
                $delete->execute();
            }
        }

        $via_url = NULL;
        $via_domain_name = NULL;

        if ($_POST) {
            if ($_POST["post"]) {
                $url = $_POST['post']['url'];
            } else {
                $url = $_POST['url'];
            }
            $original_url = $url;
            $opts = [
                "http" => [
                    "method" => "GET",
                    "header" => "Accept: text/html\r\n",
                    "user_agent" => "breakfastand.coffee"
                ]
            ];

            $html = file_get_contents($url, false, stream_context_create($opts));
            $mf = Mf2\parse($html, $url);
            $title = $_POST['title'];

            # cut off title at 15 words
            if (count(explode(" ", $title)) > 15) {
                $title = join(" ", explode(" ", $title, 16)) . " ...";
            }

            if (is_array($mf)) {
                foreach ($mf['items'] as $microformat) {
                    if ($microformat['type'][0] === "h-entry" || $microformat['type'][0] === "h-review") {
                        $title = $microformat['properties']['name'][0];
                        $like = $microformat['properties']['like-of'];
                        if (is_array($like)) {
                            foreach ($like as $item) {
                                if (in_array("h-cite", $item["type"])) {
                                    $url = $item['properties']['url'][0];
                                    $html = file_get_contents($url, false, stream_context_create($opts));
                                    $via_domain_name = parse_url($original_url, PHP_URL_HOST);
                                    $via_domain_name = preg_replace('/^www\./', '', $via_domain_name);
                                    $via_url = $original_url;
                                    break 1;
                                }
                            }
                        } else {
                            $via_domain_name = parse_url($original_url, PHP_URL_HOST);
                            $via_domain_name = preg_replace('/^www\./', '', $via_domain_name);
                            $via_url = $original_url;
                            $url = $microformat["children"][0]["properties"]["like-of"][0];
                            $title = $microformat["children"][0]["properties"]["name"][0];
                            // error_log(print_r($via_domain_name, true));
                            $html = file_get_contents($url, false, stream_context_create($opts));
                        }
                    }
                }
            }
            $url = $url ? $url : $original_url;
            $now = date('Y-m-d H:i:s');
            $tags = $_POST['tags'] ? implode(", ", (array)$_POST['tags']) : "none";
            $doc = Dom\HTMLDocument::createFromString($html, LIBXML_NOERROR);
            $title = $title ? $title : $doc->getElementsByTagName('title')->item(0)->textContent;
            $source_domain_name = parse_url($url, PHP_URL_HOST);
            $source_domain_name = preg_replace('/^www\./', '', $source_domain_name);
            if (strpos($title, "|") !== false) {
                $title = substr($title, 0, strpos($title, "|"));
            }
            insert_record($title, $url, $source_domain_name, $via_domain_name, $via_url, $tags, $now);
            echo "<section id='announcement'>Your post has been submitted! <a href='/'>Close</a></section>";
        }
        ?>
    <main>
        <header>
            <img src="https://breakfastand.coffee/mascot.png" alt="Breakfast and Coffee mascot" height="75" onmouseover="changeMascot()" onmouseout="changeMascot()" id="mascot">
            <div>
                <h1><a href="#">Breakfast <wbr />and Coffee</a></h1>
                <p><a href="/">Home</a> <a href="#submit">Submit</a> <a href="https://subscribeopenly.net/subscribe/?url=https://breakfastand.coffee">Subscribe</a></p>
            </div>
        </header>
        <p><i>Stories about breakfast, coffee, and the culture around both.</i></p>
        <section id="list">
            <ol class="h-feed">
                <?php
                    if ($_GET["via"]) {
                        $posts = $db->prepare("SELECT title, url, source, via_url, via_domain, tags, date FROM posts WHERE source = :source GROUP BY date ORDER BY date DESC LIMIT 50");
                        $posts->bindValue(':via_domain', $_GET["via"]);
                        $posts->execute();
                    } elseif ($_GET["by"]) {
                        $posts = $db->prepare("SELECT title, url, source, via_url, via_domain, tags, date FROM posts WHERE source = :source GROUP BY date ORDER BY date DESC LIMIT 50");
                        $posts->bindValue(':source', $_GET["by"]);
                        $posts->execute();
                    } else {
                        $posts = $db->query("SELECT title, url, source, via_url, via_domain, tags, date FROM posts GROUP BY date ORDER BY date DESC LIMIT 50");
                    }
                    $last_date = NULL;
                    if ($posts) {
                        while ($row = $posts->fetchArray(SQLITE3_ASSOC)) {
                            $formatted_date = date_format(date_create($row["date"]), "F jS, Y");
                            if ($last_date !== $formatted_date) {
                                echo "<li><h3>" . $formatted_date . "</h3>";
                                $last_date = $formatted_date;
                            }
                            echo "<li class='h-entry'>";
                            echo "<a href=\"" . $row["url"] . "\" class='p-name u-url'>" . $row["title"] . "</a>";
                            echo "<p>" . $row["source"] . ($row["tags"] !== "none" ? " • #" . $row["tags"] : "") . ($row["via_url"] ? " • via <a style='color: inherit;' href='" . $row["via_url"] . "'>" . $row["via_domain"] . "</a></p>" : "</p>");
                            echo "<date class='dt-published' style='display: none; visibility: hidden;'>" . date_format(date_create($row["date"]), "c") . "</date>";
                            if ($admin_pass === $_GET["pass"]) {
                                echo "<a style='display: inline;' href=\"/?delete=" . $row["url"] . "&pass=" . $admin_pass . "\">Delete</a>";
                            }
                            echo "</li>";
                        }
                    } else {
                        echo "<li>No links have been submitted to Breakfast and Coffee. <a href=\"#submit\">Submit one!</a></li>";
                    }
                ?>
            </ol>
        </section>
        <section id="submit">
            <div>
                <p style="margin-top: 0;">← <a href="#list">Back to list</a></p>
                <h2>Submit to Breakfast and Coffee</h2>
                <p>You can submit posts to Breakfast and Coffee.</p>
                <p>We especially welcome submissions about the culture of coffee, stories of coffee, and news.</p>
                <p>No promotional content. No reviews. No announcements. No press releases.</p>
                <p>You can submit either:</p>
                <ul>
                    <li>A link to a web page that you want to submit, or;</li>
                    <li>A web page that contains a <a href="https://indieweb.org/likes"><code>u-like-of</code> microformat</a>.</li>
                </ul>
            </div>
            <div>
                <form method="POST" action="/bc">
                    <label for="url">URL:</label>
                    <input type="url" id="url" name="url" required placeholder="https://example.com" />
                    <label for="title">Post title:</label>
                    <input type="text" id="title" name="title" required>
                    <label for="tags">Tags <i>(choose one or more)</i></label>
                    <label for="tags"><span>News</span> <input type="checkbox" id="tags" name="tags" value="news"></label>
                    <label for="tags">Story <input type="checkbox" id="tags" name="tags" value="story"></label>
                    <label for="tags">Video <input type="checkbox" id="tags" name="tags" value="video"></label>
                    <label for="tags">Ideas <input type="checkbox" id="tags" name="tags" value="ideas"></label>
                    <label for="tags">Project <input type="checkbox" id="tags" name="tags" value="project"></label>
                    <button type="submit">Submit</button>
                </form>
            </div>
        </section>
        <script>
            function changeMascot() {
                var mascot = document.getElementById("mascot");
                if (mascot.src === "https://breakfastand.coffee/mascot.png") {
                    mascot.src = "https://breakfastand.coffee/mascot2.png";
                } else {
                    mascot.src = "https://breakfastand.coffee/mascot.png";
                }
            }
        </script>
        <footer>
            <p> Made by <a href="https://jamesg.blog">capjamesg</a>. <a href="https://github.com/capjamesg/breakfast-and-coffee" rel="source">View source code.</a></p>
        </footer>
    </main>
</body>
</html>