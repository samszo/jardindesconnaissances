-- DELETE from flux_utitagdoc WHERE tag_id IN ()

SELECT DISTINCT t.tag_id
                  , td.tag_id id
    FROM flux_tagdoc td
         LEFT JOIN flux_tag t ON t.tag_id = td.tag_id
   WHERE t.tag_id IS NULL 


SELECT DISTINCT t.tag_id
                  , ut.tag_id id
    FROM flux_utitag ut
         LEFT JOIN flux_tag t ON t.tag_id = ut.tag_id
   WHERE t.tag_id IS NULL 

SELECT DISTINCT t.tag_id
                  ,utd.tag_id id
    FROM flux_utitagdoc utd
         LEFT JOIN flux_tag t ON t.tag_id = utd.tag_id
   WHERE t.tag_id IS NULL

SELECT DISTINCT t.tag_id
                  ,utd.tag_id id
    FROM  flux_tag 
         LEFT JOIN flux_utitagdoc utd t ON t.tag_id = utd.tag_id
   WHERE utd.tag_id IS NULL

/*
select tag_id, group_concat(distinct id) from (
SELECT DISTINCT t.tag_id
                  ,utd.tag_id id
--                  ,u.uti_id
--                  ,utd.uti_id
--                  ,d.doc_id
--                  utd.doc_id
    FROM flux_utitagdoc utd
         LEFT JOIN flux_tag t ON t.tag_id = utd.tag_id
--         LEFT JOIN flux_uti u ON u.uti_id = utd.uti_id
--         LEFT JOIN flux_doc d ON d.doc_id = utd.doc_id
   WHERE t.tag_id IS NULL -- OR d.doc_id IS NULL OR u.uti_id IS NULL
-- ORDER BY u.uti_id
 ) utfVerif
group by tag_id
*/
