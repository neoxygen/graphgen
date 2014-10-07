var some_data =
{
    "nodes": [
        {
            "id": 1
        },
        {
            "id": 2
        },
        {
            "id": 3
        }
    ],
    "edges": [
        {
            "source": 1,
            "target": 2
        },
        {
            "source": 1,
            "target": 3,
        }
    ]
};

var config = {
    dataSource: some_data
    };
alchemy.begin(config);