SELECT 
    dCat.titre,
    SUBSTRING(dCat.url, -11), dCat.url,
    dDat.titre, dDat.url,
    SUBSTRING(dDat.url, -11)    
FROM
    flux_doc dCat
    inner join flux_rapport r on r.src_id = dCat.doc_id AND r.src_obj = 'doc' and r.dst_obj='rapport' -- erreur dans l'algo and r.dst_obj='doc'
        INNER JOIN
    flux_doc dDat ON dDat.doc_id = r.dst_id
 WHERE dCat.url LIKE "http://catalogue.bnf.fr/ark:/12148/%"  AND dDat.url LIKE "http://data.bnf.fr/ark:/12148/%" 
 order by dCat.doc_id; 