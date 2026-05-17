from app.core.php_serialization import normalize_meta_value, parse_php_serialized


def test_parse_scalar_string_returns_original_value():
    assert parse_php_serialized("plain-text") == "plain-text"


def test_parse_serialized_array_returns_normalized_data():
    payload = 'a:2:{s:5:"title";s:6:"MiyaUI";s:5:"items";a:2:{i:0;s:3:"one";i:1;s:3:"two";}}'
    parsed = parse_php_serialized(payload)
    assert parsed == {"title": "MiyaUI", "items": ["one", "two"]}


def test_parse_serialized_scalars_support_bool_and_int():
    assert parse_php_serialized("b:1;") is True
    assert parse_php_serialized("i:42;") == 42


def test_normalize_meta_value_marks_malformed_input_losslessly():
    malformed = 'a:1:{s:4:"oops";s:4:"bad"'
    normalized = normalize_meta_value(malformed)
    assert normalized["status"] == "malformed"
    assert normalized["is_serialized"] is True
    assert normalized["raw"] == malformed
    assert normalized["value"] == malformed
