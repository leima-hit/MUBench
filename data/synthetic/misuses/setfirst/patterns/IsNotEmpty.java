import java.util.Set;

public class IsNotEmpty {
	public Object pattern(Set<Object> set, Object def) {
		if (!set.isEmpty()) {
			return set.iterator().next();
		} else {
			return def;
		}
	}
}
