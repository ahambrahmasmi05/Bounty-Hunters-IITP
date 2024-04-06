add_filter( "mwai_context_search", 'my_web_search', 10, 3 );
function my_web_search( $context, $query, $options = [] ) {
    // Check if the keyword 'websearch' is present in the context
    if ( ! empty( $context ) && ! in_array( 'websearch', $context ) ) {
        return $context;
    }
    
    // Get the latest question from the visitor.
    $lastMessage = $query->get_message();
    
    // Enter your API key here
    $apiKey = 'AIzaSyDpP-2xSTnI2ysQlek5rLxiaYxNxcWMoXg';

    // Enter your Custom Search Engine ID here
    $engineId = '11cf69c0eb0d54e5a';
    
    if ( empty( $apiKey ) || empty( $engineId ) ) {
        return null;
    }
    
    $url = "https://www.googleapis.com/customsearch/v1?key=$apiKey&cx=$engineId&q=" . urlencode( $lastMessage );
    
    $response = wp_remote_get( $url );
    
    if ( is_wp_error( $response ) ) {
        return null;
    }
    
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body );
    
    if ( empty( $data->items ) ) {
        return null;
    }
    
    // AI Engine expects a type (for logging purposes) and the content (which will be used by AI).
    $context["type"] = "websearch";
    $context["content"] = "";
    
    // Loop through the 5 first results.
    $max = min( count( $data->items ), 5 );
    for ( $i = 0; $i < $max; $i++ ) {
        $result = $data->items[$i];
        $title = $result->title;
        $url = $result->link;
        $snippet = $result->snippet;
        $content = "Title: $title\nExcerpt: $snippet\nURL: $url\n\n";
        $context["content"] .= $content;
    }
    
    return $context;
}