SELECT 
    r.pre_id,
    r.valeur,
    r.pre_obj,
    t.uri, t.code,
    tp.tag_id IdTagP,
    dp.doc_id
FROM
    flux_doc d
        INNER JOIN
    flux_valarnum_prod1_1.flux_doc dp ON dp.url = d.url -- and d.url = 'http://www.siv.archives-nationales.culture.gouv.fr/mm/media/download/FRAN_0023_05193_L-medium.jpg'
        INNER JOIN
    flux_rapport r ON r.src_id = d.doc_id
        AND r.src_obj = 'doc'
        AND r.dst_obj = 'tag'
        AND r.pre_obj = 'monade'
      --  AND pre_id = 4
        INNER JOIN
    flux_tag t ON t.tag_id = r.dst_id
        INNER JOIN
    flux_tag tp ON tp.tag_id = t.parent
        AND tp.code IN ('webEntities' , 'labelAnnotations')
ORDER BY dp.doc_id