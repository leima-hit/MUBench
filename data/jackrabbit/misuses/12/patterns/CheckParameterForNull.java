import org.apache.jackrabbit.core.config.RepositoryConfig;
import org.apache.jackrabbit.jca.JCAManagedConnectionFactory;

import java.io.File;

class CheckParameterForNull {
  RepositoryConfig pattern() {
    String configFile = parameters.get(JCAManagedConnectionFactory.CONFIGFILE_KEY);
    String homeDir = parameters.get(JCAManagedConnectionFactory.HOMEDIR_KEY);
    
    if (configFile != null) {
      return RepositoryConfig.create(configFile, homeDir);
    } else {
      return RepositoryConfig.create(new File(homeDir));
    }
  }
}