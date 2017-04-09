SELECT 
    dDat.titre, dDat.url,
    SUBSTRING(dDat.url, -11),
    r.rapport_id,
    count(t.code) nbTags, group_concat(t.code) tags,
    group_concat(rProp.valeur) valeurs
FROM
    flux_doc dDat
    inner join flux_rapport r on r.src_id = dDat.doc_id AND r.src_obj = 'doc' and r.dst_obj='acti' and r.dst_id = 4
    inner join flux_rapport rProp on rProp.pre_id = r.rapport_id
    inner join flux_tag t on t.tag_id = rProp.dst_id
 group by dDat.doc_id
 order by dDat.doc_id; 