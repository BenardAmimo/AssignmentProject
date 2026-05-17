 <?php

class XmlExporter {

    private static function xmlSafe($value) {
        return htmlspecialchars((string)$value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    public static function export(PDO $pdo): void {

        $courses = $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);

        $xml = new SimpleXMLElement('<courses/>');

        foreach ($courses as $c) {
            $node = $xml->addChild('course');

            $node->addChild('id', self::xmlSafe($c['id']));
            $node->addChild('code', self::xmlSafe($c['code']));
            $node->addChild('title', self::xmlSafe($c['title']));
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $dom->loadXML($xml->asXML());

        $path = __DIR__ . '/../xml/courses.xml';

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $dom->save($path);
    }
}