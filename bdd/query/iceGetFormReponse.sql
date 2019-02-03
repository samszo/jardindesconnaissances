SELECT 
	rf.*
FROM
    flux_doc dq
        INNER JOIN
    flux_rapport rf ON rf.src_id = dq.doc_id
        AND rf.src_obj = 'doc'
        AND rf.dst_obj = 'tag'
        AND rf.dst_id = 3
        AND rf.pre_obj = 'uti'
WHERE
    dq.parent = 2