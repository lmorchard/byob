<?php
/**
 * Custom feed helpers
 *
 * @package    LMO_Utils
 * @subpackage helpers
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class feed extends feed_Core
{
    /**
     * Find the location of an associated feed, given a web page URL.
     *
     * see: http://keithdevens.com/weblog/archive/2002/Jun/03/RSSAuto-DiscoveryPHP
     *
     * @param string URL to a web page
     */
    public static function find($location){

        // Try fetching HTML from the location.
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $location,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
        ));
        $html = curl_exec($ch);
        if (200 != curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            return false;
        }
        curl_close($ch);

        if(!$html or !$location){
            return false;
        }else{
            // search through the HTML, save all <link> tags
            //  and store each link's attributes in an associative array
            preg_match_all('/<link\s+(.*?)\s*\/?>/si', $html, $matches);
            $links = $matches[1];
            $final_links = array();
            $link_count = count($links);
            for($n=0; $n<$link_count; $n++){
                $attributes = preg_split('/\s+/s', $links[$n]);
                foreach($attributes as $attribute){
                    $att = preg_split('/\s*=\s*/s', $attribute, 2);
                    if(isset($att[1])){
                        $att[1] = preg_replace('/([\'"]?)(.*)\1/', '$2', $att[1]);
                        $final_link[strtolower($att[0])] = $att[1];
                    }
                }
                $final_links[$n] = $final_link;
            }
            // now figure out which one points to the RSS file
            for($n=0; $n<$link_count; $n++){
                if(strtolower($final_links[$n]['rel']) == 'alternate'){
                    if(strtolower($final_links[$n]['type']) == 'application/rss+xml'){
                        $href = $final_links[$n]['href'];
                    }
                    if(!$href and strtolower($final_links[$n]['type']) == 'text/xml'){
                        // kludge to make the first version of this still work
                        $href = $final_links[$n]['href'];
                    }
                    if($href){
                        if(strstr($href, "http://") !== false){ // if it's absolute
                            $full_url = $href;
                        }else{ // otherwise, 'absolutize' it
                            $url_parts = parse_url($location);
                            // only made it work for http:// links. Any problem with this?
                            $full_url = "http://$url_parts[host]";
                            if(isset($url_parts['port'])){
                                $full_url .= ":$url_parts[port]";
                            }
                            if($href{0} != '/'){ // it's a relative link on the domain
                                $full_url .= dirname($url_parts['path']);
                                if(substr($full_url, -1) != '/'){
                                    // if the last character isn't a '/', add it
                                    $full_url .= '/';
                                }
                            }
                            $full_url .= $href;
                        }
                        return html_entity_decode($full_url);
                    }
                }
            }
            return false;
        }
    }

}
