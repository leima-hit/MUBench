import org.apache.commons.io.IOUtils;
import java.io.IOException;
import java.io.OutputStream;
import java.io.PrintWriter;
import java.io.Writer;

public class TryIOUtilsCloseQuietly {
	public void pattern(OutputStream out, String value) throws IOException {
		Writer writer = null;
		try {
			writer = new PrintWriter(out);
			writer.write(value);
		} finally {
			IOUtils.closeQuietly(writer);
		}
	}
}
