SELECT 
    JSON_DEPTH(data),
    JSON_LENGTH(data),
    JSON_TYPE(data),
    JSON_VALID(data),
    JSON_CONTAINS_PATH(data, 'one', '$.analyzeSyntax') cp,
    JSON_EXTRACT(data,'$.analyzeSyntax[0].text.content'), -- data->'$.analyzeSyntax',
    JSON_SEARCH(data, 'all', 'content'),
    JSON_KEYS(data, '$.analyzeSyntax') c,
    data
FROM
    flux_json
ORDER BY cp
