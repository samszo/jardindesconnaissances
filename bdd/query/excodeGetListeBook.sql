SELECT 
    d.doc_id, d.titre, d.tronc, d.url, dB.doc_id bIdDoc, dB.titre bTitre, dB.tronc bTron, dB.data bData, dB.url bUrl
FROM
    flux_doc d
        INNER JOIN
    flux_rapport r ON r.src_id = d.doc_id
        AND r.src_obj = 'doc'
        AND r.dst_obj = 'tag'
        INNER JOIN
    flux_tag t ON t.code = 'liste' AND t.tag_id = r.dst_id
        INNER JOIN
    flux_rapport rB ON rB.pre_id = r.rapport_id
        AND rB.pre_obj = 'rapport'
        INNER JOIN
    flux_doc dB ON dB.doc_id = rB.dst_id;
