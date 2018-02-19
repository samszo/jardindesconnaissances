SELECT 
    exi_id, e.nom, d.doc_id, d.titre
FROM
    flux_exi e
        INNER JOIN
    flux_rapport r ON r.dst_obj = 'exi' and r.pre_obj = 'tag' and r.pre_id = 3 and r.src_obj = 'doc'
        AND r.dst_id = e.exi_id
        inner join flux_doc d on d.doc_id = r.src_id
        