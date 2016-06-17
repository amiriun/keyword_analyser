<?php

namespace AmirCup2006\GoogleKeyword;

use AmirCup2006\GoogleKeyword\exceptions\GoogleKeywordException;

/**
 * The main class for Google Keywords
 *
 * @package GoogleKeyword
 *
 * @author Amir Alian <amircup2006@gmail.com>
 *
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */
class GoogleKeyword
{
    private static $_keyword = null;
    private static $_lang = null;

    /**
     * Constructor of This class
     * This Method Created at 6/15/2016
     *
     * @param string $keyword Get Keyword
     *
     * @throws GoogleKeywordException
     *
     * @author Amir Alian <amircup2006@gmail.com>
     */
    public function __construct($keyword = null)
    {
        if (!is_null($keyword) && !is_null(self::$_keyword)) {
            $msg = "Keyword is Required ..!";
            throw new GoogleKeywordException($msg);
        }
    }

    /**
     * Magic Getter Method
     *
     * @param string $methodName Name of Method that was called
     *
     * @throws GoogleKeywordException
     *
     * @return null
     */
    public function __get($methodName)
    {
        if (method_exists($this, $methodName)) {
            return $this->{$methodName}();
        } else {
            throw new GoogleKeywordException(
                "Method " . $methodName . "() is not exists !"
            );
        }
    }

    /**
     * Sets the keyword for all future new instances
     * This Method Created at 6/15/2016
     *
     * @param string      $keyword keyword that want to search
     * @param string|null $lang    Change Language of Results
     *
     * @return $this
     */
    public static function set($keyword, $lang = null)
    {
        self::$_keyword = $keyword;
        self::$_lang = $lang;

        return new self();
    }

    /**
     * Gets Position of keyword at Results
     * This Method Created at 6/17/2016
     *
     * @param string $url URL of website
     *
     * @return array
     */
    public function findUrl($url)
    {
        $url = preg_quote($url,'/');
        $start = 0;
        do {
            $params = [
                'q'     => self::$_keyword,
                'start' => $start
            ];
            if (!is_null(self::$_lang)) {
                $params['lr'] = 'lang_' . self::$_lang;
            }
            $content = file_get_contents(
                'http://www.google.com/search?' . http_build_query($params)
            );
            preg_match(
                "/https?:\/\/(w{3}\.)?$url/i",
                $content,
                $matches
            );
            if (count($matches) == 0) {
                $start += 10;
            }
        } while (count($matches) == 0);

        return [
            'page'     => $start / 10 + 1,
            'position' => 'test'
        ];
    }

    /**
     * Gets count of Results from Google
     * This Method Created at 6/15/2016
     *
     * @return int
     */
    public function count()
    {
        $params = [
            'q' => self::$_keyword
        ];
        if (!is_null(self::$_lang)) {
            $params['lr'] = 'lang_' . self::$_lang;
        }
        $content = file_get_contents(
            'http://www.google.com/search?' . http_build_query($params)
        );
        preg_match(
            '/About (.*) results/i',
            $content,
            $matches
        );
        $count = str_replace(',', '', $matches[1]);

        return (int)$count;
    }

    /**
     * Gets Top Sites of your keyword
     * This Method Created at 6/15/2016
     *
     * @return array
     */
    public function topSites()
    {
        $params = [
            'q' => self::$_keyword
        ];
        if (!is_null(self::$_lang)) {
            $params['lr'] = 'lang_' . self::$_lang;
        }
        $content = file_get_contents(
            'http://www.google.com/search?' . http_build_query($params)
        );
        preg_match_all(
            '/(https?:\/\/' .
            '(?!schema|blogger|youtube|google|gstatic|googleusercontent)' .
            '([-\w]*\.)' .
            '(?!schema|blogger|youtube|google|gstatic|googleusercontent)' .
            '[\w\.]*)/im',
            $content,
            $matches
        );
        $topSites = array_unique($matches[0]);

        return $topSites;
    }


}
