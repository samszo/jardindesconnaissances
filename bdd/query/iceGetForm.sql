SELECT 
    df.titre,
    df.note,
    dq.doc_id,
    dq.titre,
    dq.tronc,
    dq.note,
    drp.titre,
    drp.tronc,
    drp.note
FROM
    flux_doc df
        INNER JOIN
    flux_doc dq ON dq.parent = df.doc_id
        INNER JOIN
    flux_doc drp ON drp.parent = dq.doc_id
WHERE
    df.doc_id = 3
ORDER BY drp.titre
