UPDATE flux_doc a
LEFT JOIN flux_doc b ON
    a.doc_id = b.doc_id AND MATCH (b.titre, b.note) AGAINST ("sciences+ information+ communication+")
SET
    a.score = MATCH (b.titre, b.note) AGAINST ("sciences+ information+ communication+")
